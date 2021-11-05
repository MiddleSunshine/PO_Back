<?php

class Report extends Base{
    public function Index(){
        $this->post=json_decode($this->post,1);
        $startTime=$this->post['startTime'] ?? '';
        $endTime=$this->post['endTime'] ?? '';
        empty($startTime) && $startTime=date("Y-m-d 00:00:00",strtotime("-15 day"));
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
        $dateRange=self::getDateRange($startTime,$endTime,"m-d");
        
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