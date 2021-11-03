<?php

class OKRCheckItem extends Base{
    public static $table="OKR_CheckItem";

    const STATUS_PROCESSING='new';
    const STATUS_FINISHED='finished';
    const STATUS_GIVE_UP='give_up';

    public function GetCheckItems(){
        $id=$this->get['OKR_ID'] ?? 0;
        if (!$id){
            return self::returnActionResult($this->get,false,"Param Error");
        }
        $sql=sprintf("select * from %s where OKR_ID=%d;",$id);
        return self::returnActionResult(
            [
                'OKR_CheckItem'=>$this->pdo->getRows($sql)
            ]
        );
    }
}