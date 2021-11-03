<?php

class OKR extends Base{
    public static $table="OKR";

    const STATUS_ACTIVE='Active';
    const STATUS_INACTIVE='Inactive';

    public function Index(){
        $year=$this->get['Year'] ?? date("Y");
        $month=$this->get['Month'] ?? date("M");
        $sql=sprintf("select * from %s where Year='%s' and Month='%s'",static::$table,$year,$month);
        return self::returnActionResult(
            [
                'OKR'=>$this->pdo->getRows($sql)
            ]
        );
    }
}