<?php

class ClockIn extends Base{
    public static $table="clock_in";

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
                [],
                false,
                "Checked"
            );
        }
        $this->handleSql($data,0,'ID');
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