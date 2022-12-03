<?php

class Explain extends Base{
    public static $table='Scare_Explain';

    public function getExplains($scareId){
        if (empty($scareId)){
            return [];
        }
        $sql=sprintf("select * from %s where Scare_ID=%d;",self::$table,$scareId);
        return $this->pdo->getRows($sql);
    }
}