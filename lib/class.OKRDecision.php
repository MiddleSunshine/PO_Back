<?php

class OKRDecision extends Base{
    public static $table="OKR_Decision";

    const STATUS_PROCESSING='processing';
    const STATUS_FINISHED='finished';
    const STATUS_GIVE_UP='give_up';

    public function getDecisions($ItemId,$statusMap){
        if (!is_array($statusMap)){
            $statusMap[]=$statusMap;
        }
        $status=[];
        foreach ($statusMap as $statusItem){
            $status[]=sprintf("'%s'",$statusItem);
        }
        $status=sprintf("(%s)",implode(",",$status));
        $sql=sprintf("select * from %s where OKR_Item_ID=%d and status in %s",static::$table,$ItemId,$status);
        return $this->pdo->getRows($sql);
    }
}