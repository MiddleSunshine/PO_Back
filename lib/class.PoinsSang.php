<?php

class PoinsSang extends Base{
    public $allPIDs=[];

    public function Index(){
        $PID=$this->get['PID'] ?? 0;
        $allConnection=[];
        $this->getConnection($PID,$allConnection);
        $returnData=[
            'nodes'=>[],
            'links'=>[]
        ];
        if (empty($this->allPIDs)){
            return self::returnActionResult($returnData);
        }
        $this->buildEChartData($returnData,$allConnection);
        return self::returnActionResult($returnData);
    }

    public function buildEChartData(&$returnData,&$connection){
        $sql=sprintf("select ID,keyword,status,Point,Deleted from %s where ID in (%s)",Points::$table,implode(",",array_keys($this->allPIDs)));
        $points=$this->pdo->getRows($sql,'ID');
        $singlePID=[];
        foreach ($connection as $PID=>$subPIDs){
            foreach ($subPIDs as $subPID){
                if ($points[$subPID]['Deleted']==1){
                    continue;
                }
                $source=$points[$PID]['keyword'] ?? '';
                $target=$points[$subPID]['keyword'] ?? '';
                $returnData['links'][]=[
                    'source'=>$source,
                    'target'=>$target,
                    'value'=>10
                ];
                foreach ([$subPID,$PID] as $ID){
                    if (!isset($singlePID[$ID])){
                        $subPoint=$points[$ID];
                        switch ($subPoint['status']){
                            case "new":
                                $color='#32D74B';
                                break;
                            case "solved":
                                $color="#1AA9FF";
                                break;
                            case "give_up":
                                $color="#FF453A";
                                break;
                            case "archived":
                                $color='#FF9F0A';
                                break;
                            case 'init':
                                $color='#BF5AF2';
                                break;
                            default:
                                $color='red';
                                break;
                        }
                        $returnData['nodes'][]=[
                            'name'=>$subPoint['keyword'],
                            'itemStyle'=>[
                                'color'=>$color,
                                'borderColor'=>$color
                            ]
                        ];
                        $singlePID[$ID]=1;
                    }
                }
            }
        }
    }

    public function getConnection($PID,&$returnData,$recordThisPID=false){
        static $pointConnection;
        !$pointConnection && $pointConnection=new PointsConnection();
        if (isset($this->allPIDs[$PID])){
            return false;
        }
        $this->allPIDs[$PID]=1;
        $subPIDs=$pointConnection->getSubParentId($PID);
        $recordThisPID && $returnData[$PID]=$subPIDs;
        foreach ($subPIDs as $subPID){
            $this->getConnection($subPID,$returnData,true);
            $this->allPIDs[$subPID]=1;
        }
    }
}