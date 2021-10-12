<?php

class Plan extends Base{
    public static $table="Plans";

    const STATUS_PROCESSING='new';
    const STATUS_ARCHIVED='archived';
    const STATUS_GIVE_UP='give_up';
    const STATUS_SOLVED='solved';

    const UPDATE_FINISH_TIME_SHOW='show';
    const UPDATE_FINISH_TIME_HIDDEN='hidden';

    const FULL_DAY_DEFAULT_YES='Yes';
    const FULL_DAY_DEFAULT_NO='No';

    const DISPLAY_IN_TABLE_YES='Yes';
    const DISPLAY_IN_TABLE_NO='No';

    public function GetActivePlan(){
        $sql=sprintf(
            "select ID,Name from %s where Deleted=0 and status in ('%s','%s') order by ID desc;",
            static::$table,
            self::STATUS_PROCESSING,
            self::STATUS_SOLVED
        );
        return self::returnActionResult(
            $this->pdo->getRows($sql)
        );
    }

    public function List()
    {
        $displayInTable=$this->get['DisplayInTable'] ?? self::DISPLAY_IN_TABLE_YES;
        $sql=sprintf(
            "select * from %s where Deleted=0 and status not in ('%s','%s') and Display_In_Table='%s' order by ID desc;",
            static::$table,
            self::STATUS_ARCHIVED,
            self::STATUS_GIVE_UP,
            $displayInTable
        );
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
        empty($this->post['Full_Day_Default']) && $this->post['Full_Day_Default']=self::FULL_DAY_DEFAULT_YES;
        empty($this->post['Display_In_Table']) && $this->post['Display_In_Table']=self::DISPLAY_IN_TABLE_YES;
        return $this->handleSql($this->post,$this->post['ID'],'ID');
    }
}