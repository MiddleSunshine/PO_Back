<?php

class ClockIn extends Base{
    public static $table="clock_in";

    public function Now(){
        return self::returnActionResult([
            'date'=>date("Y-m-d H:i:s")
        ]);
    }

    public function List(){
        $sql=sprintf(
            "select * from %s where Year='%s' and Month='%s';",
            self::$table,
            date("Y"),
            date("n")
        );
        $records=$this->pdo->getRows($sql);
        $amount=0;
        $list=[];
        foreach ($records as &$record){
            if (!empty($record['working_hours']) && !empty($record['off_work_time'])){
                $record['Result']=round(
                    ((strtotime($record['off_work_time'])-strtotime($record['working_hours']))-9*60*60)/60,
                    1);
            }else{
                $record['Result']=0;
            }
            $amount+=$record['Result'];
            $record['Result']=str_pad(
                $record['Result'],
                10,
                ' '
            );
            $list[$record['Month']."-".$record['Day']]=$record;
        }
        return self::returnActionResult(
            [
                'List'=>$list,
                'Amount'=>round($amount,2)
            ]
        );
    }

    public function UpdateClockIn(){
        $this->post=json_decode($this->post,1);
        $year=$this->post['Year'] ?? '';
        $month=$this->post['Month'] ?? '';
        $day=$this->post['Day'] ?? '';
        $dateKey=$this->post['Key'] ?? '';
        if (empty($year) || empty($month) || empty($day) || empty($dateKey)){
            return self::returnActionResult($this->post,false,"Data Error");
        }
        $newDate=$this->post['new_date'];
        if (!empty($newDate)){
            $minTimestamp=strtotime(sprintf("%d-%d-%d 00:00:00",$year,$month,$day));
            $maxTimestamp=strtotime(sprintf("%d-%d-%d 23:59:59",$year,$month,$day));
            $newDateTimestamp=strtotime($newDate);
            if ($newDateTimestamp<$minTimestamp || $newDateTimestamp>$maxTimestamp){
                return self::returnActionResult($this->post,false,"New Date Error");
            }
        }
        $sql=sprintf("update clock_in set %s='%s' where Year='%d' and Month='%d' and Day='%d';",$dateKey,$newDate,$year,$month,$day);
        $this->pdo->query($sql);
        return self::returnActionResult([
            $sql
        ]);
    }

    public function StartWork(){
        $data=[
            'Year'=>date("Y"),
            'Month'=>intval(date('m')),
            'Day'=>intval(date("d")),
            'working_hours'=>date("Y-m-d H:i:s")
        ];
        $record=$this->checkRecordExists($data['Year'],$data['Month'],$data['Day']);
        if ($record){
            return self::returnActionResult(
                $data,
                false,
                "Checked"
            );
        }
        $this->handleSql($data,0);
        return self::returnActionResult();
    }
    public function FinishWork(){
        $data=[
            'Year'=>date("Y"),
            'Month'=>intval(date('m')),
            'Day'=>intval(date("d")),
            'off_work_time'=>date("Y-m-d H:i:s")
        ];
        $checkRecord=$this->checkRecordExists($data['Year'],$data['Month'],$data['Day']);
        if ($checkRecord){
            $this->handleSql($data,$checkRecord['ID'],'ID',true);
            return self::returnActionResult();
        }else{
            return self::returnActionResult([],false,"Missing working_hours !!!");
        }
    }
    public function checkRecordExists($year,$month,$day){
        $sql=sprintf(
            "select * from %s where Year='%s' and Month='%s' and Day='%s';",
            self::$table,
            $year,
            $month,
            $day
        );
        return $this->pdo->getFirstRow($sql);
    }
}