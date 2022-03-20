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
        empty($startTime) && $startTime=date("Y-m-d 00:00:00");
        $sql=sprintf("select * from %s where AddTime>'%s' order by AddTime desc;",static::$table,$startTime);
        $actions=$this->pdo->getRows($sql);
        for ($i=1;$i<count($actions);$i++){
            $seconds=strtotime($actions[$i-1]['AddTime'])-strtotime($actions[$i]['AddTime']);
            $seconds=round($seconds/60,2);
            $actions[$i]['Result']=$seconds." ".($seconds>1?"mins":"min");
        }
        isset($actions[0]) && $actions[0]['Result']='';
        return self::returnActionResult(
            [
                'Actions'=>$actions
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
}