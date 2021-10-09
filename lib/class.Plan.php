<?php

class Plan extends Base{
    public static $table="Plans";

    const STATUS_PROCESSING='new';
    const STATUS_ARCHIVED='archived';
    const STATUS_GIVE_UP='give_up';
    const STATUS_SOLVED='solved';

    const UPDATE_FINISH_TIME_SHOW='show';
    const UPDATE_FINISH_TIME_HIDDEN='hidden';

    public function List()
    {
        $sql=sprintf("select * from %s where Deleted=0 and status not in ('%s','%s') order by ID desc;",static::$table,self::STATUS_ARCHIVED,self::STATUS_GIVE_UP);
        $plans=$this->pdo->getRows($sql);
        $planItem=new PlanItem();
        foreach ($plans as &$plan){
            $plan['Completion']=$planItem->getCompletion($plan['ID']);
        }
        return self::returnActionResult($plans);
    }

    public function Save(){
        $this->post=json_decode($this->post,1);
        empty($this->post['AddTime']) && $this->post['AddTime']=date("Y-m-d H:i:s");
        empty($this->post['Deleted']) && $this->post['Deleted']=0;
        empty($this->post['status']) && $this->post['status']=self::STATUS_PROCESSING;
        empty($this->post['UpdateFinishTime']) && $this->post['UpdateFinishTime']=self::UPDATE_FINISH_TIME_SHOW;
        return $this->handleSql($this->post,$this->post['ID'],'ID');
    }
}