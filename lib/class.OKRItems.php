<?php

class OKRItems extends Base{
    public static $table="OKR_Items";

    const STATUS_ACTIVE='Active';
    const STATUS_INACTIVE='Inactive';

    const TYPE_SINGLE_PLAN='single_plan';
    const TYPE_EVERY_WEEK='every_week';

    public function OKRItemData(){
        $id=$this->get['OKR_CheckItem_ID'] ?? 0;
        if (!$id){
            return self::returnActionResult($this->get,false,"Wrong Param");
        }
        $sql=sprintf("select * from %s where OKR_CheckItem_ID=%d and Deleted=0;",static::$table,$id);
        $Items=$this->pdo->getRows($sql);
        $OKRCheckResult=new OKRCheckResult();
        foreach ($Items as &$item){
            $item['CheckAble']=$item['Status']==self::STATUS_ACTIVE;
            switch ($item['type']){
                case self::TYPE_EVERY_WEEK:
                    $item['CheckResult']=$OKRCheckResult->getCheckResult($item['ID']);
                    $lastOKRCheckResult=end($item['CheckResult']);
                    if ($lastOKRCheckResult['Week'])
                    reset($item['CheckResult']);
                    break;
                case self::TYPE_SINGLE_PLAN:
                    $item['CheckResult']=$OKRCheckResult->getCheckResult($item['ID']);

            }
        }
    }
}