<?php

class Summary extends Base {
    public $statusColorMap=[
        Points::STATUS_NEW=>'red',
        Points::STATUS_SOLVED=>'green',
        Points::STATUS_ARCHIVED=>'blue',
        Points::STATUS_GIVE_UP=>'gray'
    ];
    public function calculateData($pid,&$returnData){
        $pointConnection=new PointsConnection();
        $subPointIDs=$pointConnection->getSubParentId($pid);
        if (empty($subPointIDs)){
            return $returnData;
        }
        $this->getChildren($subPointIDs,$returnData);
    }

    public function Index(){
        $pid=$this->get['pid'] ?? 0;
        $returnData=[];
        $this->calculateData($pid,$returnData);
        return self::returnActionResult($returnData);
    }

    public function Index2(){
        $pid=$this->get['pid'] ?? 0;
        $returnData=[
            'point'=>[],
            'links'=>[]
        ];
        $deep=0;
        $sql=sprintf("select SubPID from %s where PID=%d;",PointsConnection::$table,$pid);
        $PIDS=array_keys($this->pdo->getRows($sql,'SubPID'));
        while ($deep<30){
            if (empty($PIDS)){
                break;
            }
            $deep++;
            $sql=sprintf("select ID,keyword,status from %s where ID in (%s) and Deleted=0;",Points::$table,implode(",",$PIDS));
            $points=$this->pdo->getRows($sql);
            $PIDS=[];
            foreach ($points as $point){
                if (isset($returnData['point'][$point['ID']])){
                    continue;
                }

                $returnData['point'][$point['ID']]=[
                    'id'=>$point['ID'],
                    'name'=>$point['keyword'],
                    'value'=>$point['ID'],
                    'category'=>Points::$statusMap[$point['status']]
                ];
                $sql=sprintf("select PID as source,SubPID as target from %s where PID=%d;",PointsConnection::$table,$point['ID']);
                $connection=$this->pdo->getRows($sql);
                foreach ($connection as $item){
                    $PIDS[]=$item['target'];
                    $key=$item['source']."_".$item['target'];
                    if (isset($returnData['links'][$key])){
                        continue;
                    }
                    $returnData['links'][$key]=$item;
                }
            }
        }
        $returnData['links']=array_values($returnData['links']);
        $returnData['point']=array_values($returnData['point']);
        return self::returnActionResult($returnData);
    }

    public function getChildren($subPIDs,&$returnData,$deep=0){
        static $pointConnectionInstance,$pointInstance,$whitePids;
        if (!$pointConnectionInstance){
            $pointConnectionInstance=new PointsConnection();
        }
        if (!$pointInstance){
            $pointInstance=new Points();
        }
        if (!$whitePids){
            $whitePids=[];
        }

        if (empty($subPIDs)){
            return true;
        }
        if($deep>30){
            return true;
        }
        foreach ($subPIDs as $index=>$subPID){
            if (isset($whitePids[$subPID])){
                continue;
            }
            $whitePids[$subPID]=1;
            $point=$pointInstance->getPointDetail($subPID);
            $returnData[$index]=[
                'name'=>$point['ID'],
                'value'=>$point['ID'],
                'itemStyle'=>[
                    'color'=>$this->statusColorMap[$point['status']] ?? 'pink'
                ],
                'children'=>[]
            ];
            $nextSubPIDs=$pointConnectionInstance->getSubParentId($subPID);
            if (!empty($nextSubPIDs)){
                $this->getChildren($nextSubPIDs,$returnData[$index]['children'],$deep+1);
            }
        }
    }
}