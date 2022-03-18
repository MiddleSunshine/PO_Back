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
        $sql=sprintf("select ID,keyword,status,Deleted from %s where ID in (%s)",Points::$table,implode(",",$this->allPIDs));
        $points=$this->pdo->getRows($sql,'ID');
        $singlePID=[];
        foreach ($connection as $PID=>$subPIDs){
            foreach ($subPIDs as $subPID){
                $source=$points[$PID]['keyword'] ?? '';
                $target=$points[$subPID]['keyword'] ?? '';
                $returnData['links'][]=[
                    'source'=>$source,
                    'target'=>$target,
                    'value'=>$points[$subPID]['status']." / ".$points[$subPID]['Deleted']
                ];
                if (!isset($singlePID[$subPID])){
                    /**
                    new: "#32D74B",
                    solved: "#1AA9FF",
                    give_up: "#FF453A",
                    archived: "#FF9F0A",
                    init: "#BF5AF2",
                     */
                    $subPoint=$points[$subPID];
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
                            $color='BF5AF2';
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
                    $singlePID[$subPID]=1;
                }
            }
        }
    }

    public function getConnection($PID,&$returnData){
        static $pointConnection;
        !$pointConnection && $pointConnection=new PointsConnection();
        if (isset($this->allPIDs[$PID])){
            return false;
        }
        $this->allPIDs[$PID]=1;
        $subPIDs=$pointConnection->getSubParentId($PID);
        $returnData[$PID]=$subPIDs;
        foreach ($subPIDs as $subPID){
            $this->allPIDs[$subPID]=1;
            $this->getConnection($subPID,$returnData);
        }
    }
}