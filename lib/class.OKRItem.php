<?php

class OKRItem extends Base{
    public static $table="OKR_Item";

    const WEEK_STATUS_SUCCESS='success';
    const WEEK_STATUS_FAIL='fail';

    const STATUS_GIVE_UP='give_up';
    const STATUS_FINISHED='finished';
    const STATUS_PROCESSING='processing';
    const STATUS_INIT='init';

    const TYPE_WEEK='week';
    const TYPE_MONTH='month';

    public function getItems(){
        $this->post=json_decode($this->post,1);
        $OKR_ID=$this->post['OKR_ID'] ?? 0;
        if ($OKR_ID<=0){
            return self::returnActionResult(
                $this->post,
                false,
                "Param Error"
            );
        }
        if (empty($this->post['status'])){
            $status=sprintf("('%s','%s')",self::STATUS_PROCESSING,self::STATUS_INIT);
        }else{
            $status=[];
            foreach ($this->post['status'] as $statusItem){
                $status[]=sprintf("'%s'",$statusItem);
            }
            $status=sprintf("(%s)",implode(",",$status));
        }
        $sql=sprintf(
            "select * from %s where OKR_ID=%d and status in %s",
            static::$table,
            $OKR_ID,
            $status
        );
        $OKR_Items=$this->pdo->getRows($sql);
        $OKRDecision=new OKRDecision();
        foreach ($OKR_Items as &$item){
            $item['OKR_Decisions']=$OKRDecision->getDecisions($item['ID'],OKRDecision::STATUS_PROCESSING);
        }
        return self::returnActionResult(
            [
                'Items'=>$this->pdo->getRows($sql)
            ]
        );
    }
}