<?php

class GTD extends Base{
    const OFFSET_STEP=1;

    const OPTION_SUB='Sub';
    const OPTION_SAME='Same';

    public static $table="GTD";

    public function List(){
        $this->post=json_decode($this->post,1);
        $GTDCategory=new GTDCategory();
        $UsefulCategories=$GTDCategory->getProcessingCategory(
            $this->post['ShowAllCategories'] ?? true
        );
        $returnData=[];
        $showAllGTDS=$this->post['ShowAllGTD'] ?? false;
        $showFinishTimeGTD=$this->post['ShowFinishTimeGTD'] ?? false;
        $now=time();
        $filterCategory=$this->post['CategoryID'] ?? [];
        $GTDLabels=new GTDLabel();
        $usefulLabels=$GTDLabels->getUsefulLabel();
        $GTDLabelConnection=new GTDLabelConnection();
        $oneDay=24*60*60;
        foreach ($UsefulCategories as $category){
            if (!empty($filterCategory) && !in_array($category['ID'],$filterCategory)){
                continue;
            }
            $sql=sprintf("select * from %s where CategoryID=%d;",static::$table,$category['ID']);
            $GTDS=$this->pdo->getRows($sql,'CID');
            $list=[];
            $CID=0;
            while (isset($GTDS[$CID])){
                if (!empty($GTDS[$CID]['EndTime'])){
                    $endTimeStamp=strtotime($GTDS[$CID]['EndTime']);
                    $GTDS[$CID]['EndTime']=date("m-d",$endTimeStamp);
                    if ($endTimeStamp>$now){
                        if (($endTimeStamp-$now)>=$oneDay && ($endTimeStamp-$now)<=(2*$oneDay)){
                            $GTDS[$CID]['EndTime']='Tomorrow';
                        }
                        if (($endTimeStamp-$now)<=$oneDay){
                            $GTDS[$CID]['EndTime']='Today';
                        }
                    }
                }
                if (!$showFinishTimeGTD){
                    if (!$showAllGTDS){
                        if (empty($GTDS[$CID]['FinishTime'])){
                            if (!empty($GTDS[$CID]['StartTime'])){
                                if (strtotime($GTDS[$CID]['StartTime'])<=$now){
                                    $list[]=$GTDS[$CID];
                                }
                            }else{
                                $list[]=$GTDS[$CID];
                            }
                        }
                    }else{
                        $list[]=$GTDS[$CID];
                    }
                }else{
                    $list[]=$GTDS[$CID];
                }
                $CID=$GTDS[$CID]['ID'];
            }
            $category['GTDS']=array_reverse($list);
            foreach ($category['GTDS'] as &$GTD){
                $connections=$GTDLabelConnection->getLabels($GTD['ID'],0,'Label_ID');
                foreach ($connections as $lableId=>&$connection){
                    if (isset($usefulLabels[$lableId])){
                        $connection['Label']=$usefulLabels[$lableId];
                    }
                }
                $GTD['Labels']=array_values($connections);
            }
            $returnData[]=$category;
        }

        return self::returnActionResult([
            'List'=>$returnData,
            'Categories'=>$UsefulCategories
        ]);
    }

    public function Update(){
        $this->post=json_decode($this->post,1);
        if (!isset($this->post['ID'])){
            return self::returnActionResult(
                $this->post,
                false,
                "缺少参数"
            );
        }
        foreach (['FinishTime','StartTime','EndTime'] as $checkField){
            if (isset($this->post[$checkField])){
                empty($this->post[$checkField]) && $this->post[$checkField]=null;
            }
        }
        $this->handleSql($this->post,$this->post['ID']);
        return self::returnActionResult();
    }

    public function UpdateFinishTime(){
        $ID=$this->get['ID'] ?? -1;
        if ($ID<=0){
            return self::returnActionResult($this->get,false,"参数错误");
        }
        $GTD=$this->getGTD($ID,'ID','*');
        if (!empty($GTD['FinishTime'])){
            $sql=sprintf("update %s set FinishTime=null where ID=%d;",static::$table,$ID);
        }else{
            $sql=sprintf("update %s set FinishTime='%s' where ID=%d;",static::$table,date("Y-m-d H:i:s"),$ID);
        }
        $this->pdo->query($sql);
        return self::returnActionResult(
            [
                'sql'=>$sql
            ]
        );
    }

    public function RecordOffset(){
        $this->post=json_decode($this->post,1);
        $ID=$this->post['ID'] ?? -1;
        $option=$this->post['Option'] ?? "";
        if ($ID<0 || empty($option)){
            return self::returnActionResult($this->post,false,"参数错误");
        }
        $parentGTD=$this->getGTD($ID,'CID');
        $GTD=$this->getGTD($ID);
        $endGTD=$this->getFinalGTD($ID,$GTD['offset']);
        $this->updateOffset($parentGTD['ID'] ?? 0,$ID,$endGTD,$option==self::OPTION_SUB,$option==self::OPTION_SAME?(0-self::OFFSET_STEP):0);
        return self::returnActionResult([
            'ParentGTD'=>$parentGTD,
            'GTD'=>$GTD,
            'EndGTD'=>$endGTD
        ]);
    }

    public function UpdateOffsetForce(){
        $ID=$this->get['ID'] ?? -1;
        $option=$this->get['Option'] ?? '';
        if ($ID<0 || empty($option)){
            return self::returnActionResult($this->get,false,"参数错误");
        }
        $startGTD=$this->getGTD($ID);
        if (empty($startGTD)){
            return self::returnActionResult($this->get,false,"无效数据");
        }
        $endGTDID=$this->getFinalGTD($ID,$startGTD['offset']);
        $endGTD=$this->getGTD($endGTDID);
        $offsetOption=0;
        switch ($option){
            case self::OPTION_SAME:
                $offsetOption=0-self::OFFSET_STEP;
                break;
            case self::OPTION_SUB:
                $offsetOption=self::OFFSET_STEP;
                break;
        }
        if ($offsetOption==0){
            return self::returnActionResult($this->get,false,"错误数据");
        }
        if (empty($endGTD)){
            $offsetOption<0 && $offsetOption=0;
            $this->saveOffset($ID,$offsetOption);
        }else{
            $nextID=$ID;
            while ($nextID!=$endGTD['ID']){
                $nextGTD=$this->getGTD($nextID);
                $offset=$offsetOption+$nextGTD['offset'];
                $offset<0 && $offset=0;
                $this->saveOffset($nextID,$offset);
                $nextID=$nextGTD['CID'];
            }
            $offset=$offsetOption+$endGTD['offset'];
            $offset<0 && $offset=0;
            $this->saveOffset($endGTD['ID'],$offset);
        }
        return self::returnActionResult();
    }

    public function UpdateCID(){
        $this->post=json_decode($this->post,true);
        $ID=$this->post['ID'] ?? -1;
        $PID=$this->post['PID'] ?? -1;
        $option=$this->post['Option'] ?? '';
        $categoryID=$this->post['CategoryID'] ?? -1;
        if ($ID<0 || $PID<0 || empty($option) || $categoryID<0){
            return self::returnActionResult(
                $this->post,
                false,
                "参数错误"
            );
        }
        $debug=[];
        // Step 1:将移动的队列剥离出来
        $gtdList2Start=$this->getGTD($ID);
        $gtdList2EndId=$this->getFinalGTD($ID,$gtdList2Start['offset']);
        // 中间剥离出来的队列
        $GTDList2=new GTDList(
            $gtdList2Start,
            $this->getGTD(
                $gtdList2EndId
            )
        );
        // 剩余的头部队列
        $GTDList1=new GTDList(
            $this->getGTDEnd($categoryID),
            $this->getGTD($ID,'CID')
        );
        // 剩余队列的尾部
        $GTDList3=new GTDList(
            $this->getGTD($GTDList2->end['CID'] ?? 0)
        );
        // Step 2：将剩余的队列首先组合起来
        if (($GTDList1->end['ID'] ?? -1)>0){
            if (($GTDList3->start['ID'] ?? -1)>0){
                $debug[]=1;
                $this->saveCID($GTDList1->end['ID'],$GTDList3->start['ID']);
            }else{
                $debug[]=1.1;
                $this->saveCID($GTDList1->end['ID'],0);
            }
        }
        // Step 3：将队列插入到刚才组合起来的队列中
        if (!empty($PID)){
            $debug[]=2;
            $a=$this->getGTD($PID);
            $this->saveCID($PID,$ID);
            if ($GTDList2->end['ID'] ?? -1){
                $debug[]=3;
                $this->saveCID($GTDList2->end['ID'],$a['CID']);
            }else{
                $debug[]=4;
                $this->saveCID($ID,$a['CID']);
            }
        }else{
            if ($ID!=$GTDList1->start['ID']){
                $this->saveCID($ID,$GTDList1->start['ID']);
                $debug[]=5;
            }
        }
        // 更新 offset
        $this->updateOffset(
            $PID,
            $GTDList2->start['ID'],
            $GTDList2->end['ID'],
            $option==self::OPTION_SUB
        );
        return self::returnActionResult(
            [
                'debug'=>$debug,
                '1'=>[
                    $GTDList1->start,
                    $GTDList1->end
                ],
                '2'=>[
                    $GTDList2->start,
                    $GTDList2->end
                ],
                '3'=>[
                    $GTDList3->start,
                    $GTDList3->end
                ]
            ]
        );
    }

    public function UpdateCategory(){
        $this->post=json_decode($this->post,true);
        $ID=$this->post['ID'] ?? -1;
        $PID=$this->post['PID'] ?? -1;
        $option=$this->post['Option'] ?? '';
        $categoryID=$this->post['CategoryID'] ?? -1;
        if ($ID<0 || $PID<0 || empty($option) || $categoryID<0){
            return self::returnActionResult(
                $this->post,
                false,
                "参数错误"
            );
        }
        // Step 1:Move this part to the end
        $startGTD=$this->getGTD($ID,'ID','*');
        if (empty($startGTD)){
            return self::returnActionResult($this->post,false,"Wrong Data");
        }
        $endGTD=$this->getGTDEnd($startGTD['CategoryID']);
        $this->post=json_encode(
            [
                'ID'=>$ID,
                'PID'=>0,
                'CategoryID'=>$startGTD['CategoryID'],
                'Option'=>self::OPTION_SAME
            ]
        );
        $stepResult=$this->UpdateCID();
        if (!$stepResult['Status']){
            return $stepResult;
        }
        // Step 2:Update Category
        $nextID=$ID;
        $endID=$this->getFinalGTD($ID,$startGTD['offset']);
        !$endID && $endID=$ID;
        $ids=[];
        while ($endID!=$nextID){
            $nextGTD=$this->getGTD($nextID);
            if (!empty($nextGTD)){
                $ids[]=$nextID;
                $nextID=$nextGTD['CID'];
            }else{
                break;
            }
        }
        $ids[]=$endID;
        $sql=sprintf("update %s set CategoryID=%d where ID in (%s);",static::$table,$categoryID,implode(",",$ids));
        $this->pdo->query($sql);
        // Step 3:move this part to new Category List Start
        $newListEndGTD=$this->getGTDEnd($categoryID);
        if (!empty($endGTD['ID']) && !empty($newListEndGTD['ID'])){
            $this->saveCID($endGTD['ID'],$newListEndGTD['ID']);
        }
        // Step 4:move to the PID
        $this->post=json_encode([
            'ID'=>$ID,
            'PID'=>$PID,
            'CategoryID'=>$categoryID,
            'Option'=>$option
        ]);
        return $this->UpdateCID();
    }

    public function CreateNewGTD(){
        $this->post=json_decode($this->post,1);
        $PID=$this->post['PID'] ?? -1;
        $CategoryID=$this->post['CategoryID'] ?? -1;
        if ($PID<0 || $CategoryID<0){
            return self::returnActionResult($this->get,false,"参数错误！");
        }
        if ($PID==0){
            $GTDTop=$this->getGTDEnd($CategoryID);
            $sql=[
                'AddTime'=>date("Y-m-d H:i:s"),
                'offset'=>0,
                'CategoryID'=>$CategoryID,
                'CID'=>empty($GTDTop)?0:$GTDTop['ID']
            ];
        }else{
            $GTD=$this->getGTD($PID,'ID','*');
            $sql=[
                'AddTime'=>date("Y-m-d H:i:s"),
                'offset'=>$GTD['offset'],
                'CID'=>$GTD['CID'] ?? 0,
                'CategoryID'=>$GTD['CategoryID']
            ];
        }
        $dataBaseOptionResult=$this->handleSql($sql,0,'',true);
        $newGTDID=$dataBaseOptionResult['Data']['ID'];
        $GTD=$this->getGTD($newGTDID,'ID','*');
        $this->saveCID($PID,$newGTDID);
        return self::returnActionResult(
            [
                'NewItem'=>$GTD
            ]
        );
    }

    public function GetGTDHistory(){
        $timeRange=$this->get['Days'] ?? date("w");
        $timeRange==0 && $timeRange=7;
        $FinishGTDS=[];
        $sql=sprintf("select * from %s",GTDCategory::$table);
        $Categories=$this->pdo->getRows($sql,'ID');
        for ($day=0;$day<$timeRange;$day++){
            $date=date("Y-m-d",strtotime("-{$day} day"));
            $sql=sprintf("select * from %s where FinishTime between '%s 00:00:00' and '%s 23:59:59' order by FinishTime desc;",
                static::$table,$date,$date
            );
            // todo 增加 Category 的信息
            $GTDs=$this->pdo->getRows($sql);
            $FinishGTDS[$day]=[];
            $FinishGTDS[$day]=[
                'Date'=>substr($date,5),
                'GTDs'=>$GTDs
            ];
        }
        $FinishGTDS[0]['Date']="Today";
        isset($FinishGTDS[1]) && $FinishGTDS[1]['Date']='Yesterday';
        return self::returnActionResult(
            [
                'List'=>array_values($FinishGTDS)
            ]
        );
    }

    public function getFinalGTD($ID,$limitOffset){
        $GTD=$this->getGTD($ID);
        if (!empty($GTD['CID'])){
            $nextGTD=$this->getGTD($GTD['CID']);
            if (!empty($nextGTD)){
                if ($nextGTD['offset']>$limitOffset){
                    return $this->getFinalGTD($nextGTD['ID'],$limitOffset);
                }else{
                    return $GTD['ID'];
                }
            }
        }else{
            return $GTD['ID'];
        }
    }

    public function updateOffset($ID,$startId,$endId,$sub=true,$extraOffset=0){
        $GTD=$this->getGTD($ID);
        !isset($GTD['offset']) && $GTD['offset']=0;
        if ($sub){
            $baseOffset=$GTD['offset']+self::OFFSET_STEP+$extraOffset;
        }else{
            $baseOffset=$GTD['offset']+$extraOffset;
        }
        if (empty($endId)){
            $this->saveOffset($startId,$baseOffset);
        }else{
            $endlessPrevent=1000;
            $nextID=$startId;
            $firstGTD=$this->getGTD($startId);
            while ($nextID!=$endId && $endlessPrevent>0){
                $endlessPrevent--;
                $subGTD=$this->getGTD($nextID);
                if (empty($subGTD)){
                    break;
                }
                $this->saveOffset($nextID,$subGTD['offset']-$firstGTD['offset']+$baseOffset);
                $nextID=$subGTD['CID'];
            }
            // update $endId
            $endGTD=$this->getGTD($endId);
            $this->saveOffset($endId,$endGTD['offset']-$firstGTD['offset']+$baseOffset);
        }
    }

    public function saveCID($ID,$NewCID){
        $sql=sprintf("update %s set CID=%d where ID=%d;",static::$table,$NewCID,$ID);
        return $this->pdo->query($sql);
    }

    public function saveOffset($id,$offset){
        $sql=sprintf("update %s set offset=%d where ID=%d;",static::$table,$offset,$id);
        $this->pdo->query($sql);
    }

    public function getGTDEnd($categoryId){
        $sql=sprintf("select ID,CID from %s where CategoryID=%d;",static::$table,$categoryId);
        $GTDs=$this->pdo->getRows($sql,'CID');
        $CID=0;
        $endlessPrevent=0;
        while (isset($GTDs[$CID]) && $endlessPrevent<10000){
            $endlessPrevent++;
            $CID=$GTDs[$CID]['ID'];
        }
        return $this->getGTD($CID,'ID','*');
    }

    public function getGTD($ID,$IDType='ID',$field='ID,CID,offset'){
        $sql=sprintf("select %s from %s where %s=%d;",$field,static::$table,$IDType,$ID);
        return $this->pdo->getFirstRow($sql);
    }
}

class GTDList{
    public $start;
    public $end;
    public function __construct($start,$end=[])
    {
        $this->start=$start;
        $this->end=$end;
    }
}