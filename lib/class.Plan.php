<?php

class Plan extends Base{
    public static $table="Plans";

    public function List()
    {
        $sql=sprintf("select * from %s where Deleted=0 order by ID desc;",static::$table);
        $plans=$this->pdo->getRows($sql);
        $planItem=new PlanItem();
        foreach ($plans as &$plan){
            $plan['Completion']=$planItem->getCompletion($plan['ID']);
        }
        return self::returnActionResult($plans);
    }
}