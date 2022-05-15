<?php

class Actions extends Base{
    public static $table='Actions';

    const QUICK_INPUT='quick_input';

    public function NewAction(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['Title'])){
            return self::returnActionResult($this->post,false,"");
        }
        $this->post['AddTime']=date("Y-m-d H:i:s");
        $this->handleSql($this->post,0);
        return self::returnActionResult($this->post);
    }

    public function List()
    {
        $startTime=$this->get['StartTime'] ?? '';
        $endTime=$this->get['EndTime'] ?? '';
        return self::returnActionResult(
            [
                'Actions'=>$this->getActions($startTime,$endTime)
            ]
        );
    }

    public function ActionSummary(){
        $startTime=$this->get['StartTime'] ?? "";
        $endTime=$this->get['EndTime'] ?? '';
        $actions=$this->getActions($startTime,$endTime);
        $summary=[];
        foreach ($actions as $action){
            !isset($summary[$action['Title']]) && $summary[$action['Title']]=0;
            $summary[$action['Title']]+=$action['Result'];
        }
        natsort($summary);
        $returnData=[];
        $amount=0;
        foreach ($summary as $title=>$result){
            $amount+=$result;
            $returnData[]=[
                'Title'=>$title,
                'Result'=>$result
            ];
        }
        return self::returnActionResult(
            [
                'Summary'=>$returnData,
                'Amount'=>$amount
            ]
        );
    }

    public function DistinctActions(){
        $sql=sprintf("select distinct Title from %s where QuickInput='%s';",static::$table,self::QUICK_INPUT);
        return self::returnActionResult(
            [
                'Actions'=>$this->pdo->getRows($sql)
            ]
        );
    }

    public function getActions($startTime,$endTime){
        empty($startTime) && $startTime=date("Y-m-d 00:00:00");
        empty($endTime) && $endTime=date("Y-m-d 23:59:59");
        $sql=sprintf("select * from %s where AddTime between '%s' and '%s' order by AddTime desc;",static::$table,$startTime,$endTime);
        $actions=$this->pdo->getRows($sql);
        if (empty($actions)){
            return $actions;
        }
        for ($i=1;$i<count($actions);$i++){
            $seconds=strtotime($actions[$i-1]['AddTime'])-strtotime($actions[$i]['AddTime']);
            $seconds=round($seconds/60,2);
            $actions[$i]['Result']=$seconds;
        }
        isset($actions[0]) && $actions[0]['Result']=0;
        return $actions;
    }
}