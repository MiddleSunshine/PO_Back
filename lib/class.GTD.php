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
        $now=time();
        $filterCategory=$this->post['CategoryID'] ?? [];
        foreach ($UsefulCategories as $category){
            if (!empty($filterCategory) && !in_array($category['ID'],$filterCategory)){
                continue;
            }
            $sql=sprintf("select * from %s where CategoryID=%d;",static::$table,$category['ID']);
            $GTDS=$this->pdo->getRows($sql,'CID');
            $list=[];
            $CID=0;
            while (isset($GTDS[$CID])){
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
                $CID=$GTDS[$CID]['ID'];
            }
            $category['GTDS']=array_reverse($list);
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
        $this->handleSql($this->post,$this->post['ID'],'');
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
        $this->updateOffset($parentGTD['ID'] ?? 0,$ID,$endGTD,$option==self::OPTION_SUB,$option==self::OPTION_SAME?-1:0);
        return self::returnActionResult([
            'ParentGTD'=>$parentGTD,
            'GTD'=>$GTD,
            'EndGTD'=>$endGTD
        ]);
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
            $this->saveCID($ID,$GTDList1->start['ID']);
            $debug[]=5;
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
        !isset($GTD['offset']) && $GTD['offset']=0-self::OFFSET_STEP;
        if ($sub){
            $baseOffset=$GTD['offset']+self::OFFSET_STEP+$extraOffset;
        }else{
            $baseOffset=$GTD['offset'];
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
        return $this->getGTD($CID);
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