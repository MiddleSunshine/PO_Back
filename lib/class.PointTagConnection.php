<?php

class PointTagConnection extends Base{
    public static $table="PS_Tag_Connection";

    public function NewConnection(){
        $this->post=json_decode($this->post,true);
        if(empty($this->post['PID']) || empty($this->post['TID'])){
            return self::returnActionResult($this->post,false,"参数错误");
        }
        if($this->checkConnection($this->post['PID'],$this->post['TID'])){
            return self::returnActionResult(
                $this->post
            );
        }
        $this->createConnection($this->post['PID'],$this->post['TID']);
        return self::returnActionResult();
    }

    public function DeleteConnection(){
        $this->post=json_decode($this->post,true);
        if(empty($this->post['PID']) || empty($this->post['TID'])){
            return self::returnActionResult($this->post,false,"参数错误");
        }
        $sql=sprintf("delete from %s where PID=%d and TID=%d;",static::$table,$this->post['PID'],$this->post['TID']);
        $this->pdo->query($sql);
        if($this->checkConnection($this->post['PID'],$this->post['TID'])){
            return self::returnActionResult(
                $this->post,
                false,
                "删除对应关系失败"
            );
        }
        return self::returnActionResult();
    }


    public function createConnection($PID,$TID){
        if(empty($PID) || empty($TID)){
            return false;
        }
        $data=[
            'PID'=>$PID,
            'TID'=>$TID
        ];
        $this->handleSql($data,0);
        return true;
    }

    public function checkConnection($PID,$TID){
        if(empty($PID) || empty($TID)){
            return false;
        }
        $sql=sprintf("select * from %s where PID=%d and TID=%d",static::$table,$PID,$TID);
        return $this->pdo->getFirstRow($sql);
    }

    public function getSummaryTagConnection($summaryId){
        $sql=sprintf("select * from %s where PID=%d;",static::$table,$summaryId);
        $TagIds=$this->pdo->getRows($sql,'TID');
        return array_keys($TagIds);
    }
}