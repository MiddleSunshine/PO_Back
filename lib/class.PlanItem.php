<?php

class PlanItem extends Base{
    public static $table="Plan_Item";

    public function getCompletion($PID){
        $sql=sprintf("select FinishTime as number from %s where PID=%d and Deleted=0;",static::$table,$PID);
        $planItem=$this->pdo->getRows($sql);
        $count=[
            'Finished'=>0,
            'UnFinished'=>0
        ];
        foreach ($planItem as $item){
            if (!empty($item['FinishTime'])){
                $count['Finished']++;
            }else{
                $count['UnFinished']++;
            }
        }
        if ($count['Finished']==0 && $count['UnFinished']==0){
            return 0;
        }
        return intval(
            ($count['Finished']/($count['Finished']+$count['UnFinished']))*100
        );
    }
}