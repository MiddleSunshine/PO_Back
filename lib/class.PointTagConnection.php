<?php

class PointTagConnection extends Base{
    public static $table="PS_Tag_Connection";

    public function getSummaryTagConnection($summaryId){
        $sql=sprintf("select * from %s where PID=%d;",static::$table,$summaryId);
        $TagIds=$this->pdo->getRows($sql,'TID');
        return array_keys($TagIds);
    }
}