<?php

class Report extends Base{
    public function Index(){
        $this->post=json_decode($this->post,1);
        $startTime=$this->post['startTime'] ?? '';
        $endTime=$this->post['endTime'] ?? '';
        empty($startTime) && $startTime=date("Y-m-d 00:00:00",strtotime("-7 day"));
        empty($endTime) && $endTime=date("Y-m-d 23:59:59");
        $page=1;
        $pageSize=5000;
        $returnData=[];
        while ($page<100){
            $sql=sprintf(
                "select status,LastUpdateTime from %s where LastUpdateTime between '%s' and '%s' and Deleted=0 limit %d,%d",
                Points::$table,$startTime,$endTime,($page-1)*$pageSize,$pageSize
            );
            $page++;
            $points=$this->pdo->getRows($sql);
            if (empty($points)){
                break;
            }
            foreach ($points as $point){
                $date=date("m-d",strtotime($point['LastUpdateTime']));
                $status=$point['status'];
                if (!isset($returnData[$status])){
                    $returnData[$status]=[];
                }
                if (!isset($returnData[$status][$date])){
                    $returnData[$status][$date]=0;
                }
                $returnData[$status][$date]++;
            }
        }
        $dateRange=self::getDateRange($startTime,$endTime,"m-d");
        $points=[];
        $outsideIndex=0;
        foreach ($returnData as $status=>$amount){
            $points[$outsideIndex]=[
                'name'=>$status,
                'type'=>'bar',
                'data'=>[]
            ];
            foreach ($dateRange as $index=>$date){
                $points[$outsideIndex]['data'][$index]=$amount[$date] ?? 0;
            }
            $outsideIndex++;
        }
        unset($returnData);
        return self::returnActionResult([
            'points'=>$points,
            'xData'=>$dateRange,
            'startTime'=>$startTime,
            'endTime'=>$endTime,
            'post'=>$this->post
        ]);
    }

    public function Points(){
        $this->post=json_decode($this->post,1);
        $startTime=$this->post['startTime'] ?? '';
        $endTime=$this->post['endTime'] ?? '';
        empty($startTime) && $startTime=date("Y-m-d 00:00:00",strtotime("-15 day"));
        empty($endTime) && $endTime=date("Y-m-d 23:59:59");
        $page=1;
        $pageSize=5000;
        $returnData=[];
        $sql=sprintf(
            "select sum(Point) as amount from %s where LastUpdateTime<'%s' and status in ('%s','%s');",
            Points::$table,
            $startTime,
            Points::STATUS_SOLVED,
            Points::STATUS_ARCHIVED
        );
        $basePointAmount=$this->pdo->getFirstRow($sql);
        $sql=sprintf(
            "select sum(Point) as amount from %s where LastUpdateTime<'%s' and status='%s'",
            Willing::$table,
            $startTime,
            Willing::STATUS_EXCHANGED
        );
        $baseWillingAmount=$this->pdo->getFirstRow($sql);
        while ($page<100){
            $sql=sprintf(
                "select LastUpdateTime,Point,status from %s where LastUpdateTime between '%s' and '%s' and Deleted=0 limit %d,%d",
                Points::$table,$startTime,$endTime,($page-1)*$pageSize,$pageSize
            );
            $page++;
            $points=$this->pdo->getRows($sql);
            if (empty($points)){
                break;
            }
            foreach ($points as $point){
                $date=date("m-d",strtotime($point['LastUpdateTime']));
                !isset($returnData[$date]) && $returnData[$date]=0;
                switch ($point['status']){
                    case Points::STATUS_SOLVED:
                    case Points::STATUS_ARCHIVED:
                        $returnData[$date]+=$point['Point'];
                        break;
                }
            }
        }
        $sql=sprintf("select * from %s where status='%s'",Willing::$table,Willing::STATUS_EXCHANGED);
        $willings=$this->pdo->getRows($sql);
        $willingData=[];
        foreach ($willings as $willing){
            $date=date("m-d",strtotime($willing['LastUpdateTime']));
            $willingData[$date]=$willing['Point'];
        }
        $dateRange=self::getDateRange($startTime,$endTime,"m-d");
        $point=$willingAmount=$pointAmount=$exchangeAblePoint=[];
        $amount=$basePointAmount['amount'] ?? 0;
        $sumOfWilling=$baseWillingAmount['amount'] ?? 0;
        foreach ($dateRange as $date){
            $point[]=$returnData[$date] ?? 0;
            $sumOfWilling+=$willingData[$date] ?? 0;
            $willingAmount[]=$willingData[$date] ?? 0;
            $amount+=$returnData[$date] ?? 0;
            $pointAmount[]=$amount;
            $exchangeAblePoint[]=$amount-$sumOfWilling;
        }
        return self::returnActionResult(
            [
                'point'=>[
                    // [
                    //     'data'=>$pointAmount,
                    //     'name'=>'Point Amount',
                    //     'type'=>'line'
                    // ],
                    [
                        'data'=>$point,
                        'name'=>'Point',
                        'type'=>'line',
                        // itemStyle : { normal: {label : {show: true}}}
                        'itemStyle'=>[
                            'normal'=>[
                                'label'=>[
                                    'show'=>true
                                ]
                            ]
                        ]
                    ],
//                    [
//                        'data'=>$willingAmount,
//                        'name'=>'Willing',
//                        'type'=>'line'
//                    ],
//                    [
//                        'data'=>$exchangeAblePoint,
//                        'name'=>'Point Rest',
//                        'type'=>'line'
//                    ]
                ],
                'xData'=>$dateRange,
                'StartTime'=>$startTime,
                'EndTime'=>$endTime,
                'post'=>$this->post
            ]
        );
    }

    public function GetPercent(){
        $sql=sprintf("select count(*) as number,status from %s where Deleted=0 group by status;",Points::$table);
        $count=$this->pdo->getRows($sql);
        $returnData=[];
        foreach ($count as $value){
            $returnData[]=[
                'value'=>intval($value['number']),
                'name'=>$value['status']
            ];
        }
        return self::returnActionResult($returnData);
    }
}