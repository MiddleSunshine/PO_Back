<?php

class OKRRule extends Base{
    public static $table="OKR_Rule";

    const STATUS_ACTIVE='active';
    const STATUS_INACTIVE='inactive';

    public function NewRule(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['Rule'])){
            return self::returnActionResult(
                $this->post,
                false,
                "Wrong Param"
            );
        }
        $this->post['Status']=self::STATUS_ACTIVE;
        $this->post['AddTime']=date("Y-m-d H:i:s");
        $this->post['Year']=date("Y");
        $this->post['Month']=date("n");
        return $this->handleSql($this->post,0);
    }
}