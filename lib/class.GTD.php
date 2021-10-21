<?php

class GTDList{
    public $start;
    public $end;
    public function __construct($start,$end=[])
    {
        $this->start=$start;
        $this->end=$end;
    }
}

class GTD extends Base{
    const OFFSET_STEP=1;
    public static $table="GTD";
    public function List(){
        $this->post=json_decode($this->post,1);
        $where=[];
        if (!empty($this->post['CategoryID'])){
            $where[]=sprintf("CategoryID in (%s)",$this->post['CategoryID']);
        }
        $sql=sprintf("
            select * from %s 
        ",static::$table);
        if (!empty($where)){
            $sql.=" where ".implode("and ",$where);
        }
        $GTDS=$this->pdo->getRows($sql,'CID');
        $isShowAll=$this->post['ShowAll'] ?? false;
        // 如果并不展示全部数据，那么就要过滤掉一些数据
        if (!$isShowAll){
            $CID=0;
            $endlessPrevent=0;
            while (isset($GTDS[$CID]) && $endlessPrevent<100000){
                $endlessPrevent++;
                // 本次考虑的 $GTD
                $thisTurnGTD=$GTDS[$CID];
                // 其下一个的GTD
                $sonGTD=$GTDS[$thisTurnGTD['CID']] ?? ['offset'=>-1];
                if (!empty($thisTurnGTD['FinishTime'])){
                    if($thisTurnGTD['offset']>=$sonGTD['offset']){
                        unset($GTDS[$CID]);
                    }else{
                        if (!empty($sonGTD['FinishTime'])){
                            unset($GTDS[$CID]);
                        }
                    }
                }
                $CID=$sonGTD['ID'];
            }
        }
        return self::returnActionResult([
            'List'=>$GTDS
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
        if ($option!='Same'){
            $this->updateOffset(
                $PID,
                $GTDList2->start['ID'],
                $GTDList2->end['ID']
            );
        }
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
        $PID=$this->get['PID'] ?? -1;
        $CategoryID=$this->get['CategoryID'] ?? -1;
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
                    return $nextGTD['ID'];
                }
            }
        }else{
            return $GTD['ID'];
        }
    }

    public function updateOffset($ID,$startId,$endId){
        $GTD=$this->getGTD($ID);
        // ID=0 的情况
        !isset($GTD['offset']) && $GTD['offset']=0-self::OFFSET_STEP;
        if (empty($endId)){
            $this->saveOffset($startId,$GTD['offset']+self::OFFSET_STEP);
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
                $this->saveOffset($nextID,$subGTD['offset']-$firstGTD['offset']+$GTD['offset']+self::OFFSET_STEP);
            }
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