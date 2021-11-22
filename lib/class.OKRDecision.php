<?php

class OKRDecision extends Base{
    public static $table="OKR_Decision";

    const STATUS_PROCESSING='processing';
    const STATUS_FINISHED='finished';
    const STATUS_GIVE_UP='give_up';

    public function NewDecision(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['Content']) || empty($this->post['OKR_Item_ID'])){
            return self::returnActionResult(
                $this->post,
                false,
                "Please Input The Content !"
            );
        }
        $this->post['status']=self::STATUS_PROCESSING;
        $this->post['AddTime']=date("Y-m-d H:i:s");
        return $this->handleSql($this->post,0);
    }

    public function getDecisions($ItemId,$statusMap){
        if (empty($statusMap)){
            $sql=sprintf("select * from %s where OKR_Item_ID=%d;",static::$table,$ItemId);
        }else{
            $status=[];
            foreach ($statusMap as $statusItem){
                $status[]=sprintf("'%s'",$statusItem);
            }
            $status=sprintf("(%s)",implode(",",$status));
            $sql=sprintf("select * from %s where OKR_Item_ID=%d and status in %s",static::$table,$ItemId,$status);
        }
        return $this->pdo->getRows($sql);
    }
}