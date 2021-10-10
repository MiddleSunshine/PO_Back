<?php

class PointsConnection extends Base{
    public static $table="Points_Connection";
    public function getSubParentId($pid){
        $sql=sprintf("select SubPID from %s where PID=%d;",static::$table,$pid);
        $dataBaseData=$this->pdo->getRows($sql);
        return array_column($dataBaseData,'SubPID');
    }

    public function updatePointsConnection($pid,$subPID){
        $sql=sprintf("select * from %s where PID=%d and SubPID=%d",static::$table,$pid,$subPID);
        $connection=$this->pdo->getFirstRow($sql);
        if ($connection){
            return true;
        }
        $sql=sprintf("insert into %s(PID,SubPID) value (%d,%d);",static::$table,$pid,$subPID);
        return $this->pdo->query($sql);
    }
}