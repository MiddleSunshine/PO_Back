<?php

class OKRCheckResult extends Base{
    public static $table="OKR_CheckResult";

    public function getCheckResult($OKR_CheckItem_ID,$getMultiple=true){
        $sql=sprintf("select * from %s where OKR_CheckItem_ID=%d order by ID desc;",static::$table,$OKR_CheckItem_ID);
        if ($getMultiple){
            return $this->pdo->getRows($sql);
        }else{
            return $this->pdo->getFirstRow($sql);
        }
    }
}