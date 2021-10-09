<?php

class PlanItem extends Base{
    public static $table="Plan_Item";

    public function GetPlanItems(){
        $pid=$this->get['PID'] ?? 0;
        $sql=sprintf("select * from %s where PID=%d and Deleted=0;",static::$table,$pid);
        $planItems=$this->pdo->getRows($sql,'PPID');
        $returnData=[];
        $PPID=0;
        // 按照 PPID 的链表进行排序
        while (isset($planItems[$PPID])){
            $returnData[]=$planItems[$PPID];
            $nextPPID=$planItems[$PPID]['ID'];
            unset($planItems[$PPID]);
            $PPID=$nextPPID;
        }
        empty($returnData) && $returnData=[[]];
        return self::returnActionResult(
            [
                'Table'=>$returnData,
                'Completion'=>$this->getCompletion($pid)
            ]
        );
    }

    public function Save(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['PID'])){
            return self::returnActionResult([],false,"缺少PID");
        }
        empty($this->post['AddTime']) && $this->post['AddTime']=date("Y-m-d H:i:s");
        empty($this->post['FinishTime']) && $this->post['FinishTime']=null;
        empty($this->post['PPID']) && $this->post['PPID']=0;
        empty($this->post['Deleted']) && $this->post['Deleted']=0;
        if (!empty($this->post['PPID']) && empty($this->post['ID'])){
            // 在任意位置插入一个新的值
            $sql=sprintf(
                "select * from %s where PPID=%d and PID=%d and Deleted=0;",
                static::$table,
                $this->post['PPID'],
                $this->post['PID']
            );
            $historyPlanItem=$this->pdo->getFirstRow($sql);
            // 在同一个位置有历史值
            if (!empty($historyPlanItem)){
                // 先保存当前值
                $returnData=$this->handleSql($this->post,$this->post['ID'],'ID',true);
                if (!empty($returnData['Data']['ID'])){
                    // 更新历史数据的 PPID，使其指向新的 Plan_Item 数据
                    $historyPlanItem['PPID']=$returnData['Data']['ID'];
                    $this->handleSql($historyPlanItem,$historyPlanItem['ID'],'ID');
                    return $returnData;
                }
            }
        }
        if ($this->post['Deleted']==1 && !empty($this->post['ID'])){
            // 删除任意位置的一个值
            $sql=sprintf("select * from %s where ID=%d;",static::$table,$this->post['ID']);
            $planItem=$this->pdo->getFirstRow($sql);
            if (!empty($planItem)){
                $sql=sprintf("select * from %s where PPID=%d and Deleted=0;",static::$table,$planItem['ID']);
                $nextPlanItem=$this->pdo->getFirstRow($sql);
                //
                $nextPlanItem['PPID']=$planItem['PPID'];
                $this->handleSql($nextPlanItem,$nextPlanItem['ID'],'ID');
            }
        }
        return $this->handleSql($this->post,$this->post['ID'],'ID');
    }

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