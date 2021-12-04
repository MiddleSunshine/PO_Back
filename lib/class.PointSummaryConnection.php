<?php

class PointSummaryConnection extends Base{
    public static $table="PS_Connection";

    public function NewConnection(){
        $this->post=json_decode($this->post,1);
        if(empty($this->post['SID']) || empty($this->post['PID'])){
            return self::returnActionResult($this->post,false,"参数错误");
        }
        if($this->checkConnection($this->post['SID'],$this->post['PID'])){
            return self::returnActionResult($this->post);
        }
        $this->createNewConnection($this->post['SID'],$this->post['PID']);
        return self::returnActionResult($this->post);
    }

    public function Delete(){
        $this->post=json_decode($this->post,1);
        if(empty($this->post['SID']) || empty($this->post['PID'])){
            return self::returnActionResult($this->post,false,"参数错误");
        }
        if($this->deleteConnection($this->post['SID'],$this->post['PID'])){
            return self::returnActionResult();
        }else{
            return self::returnActionResult(
                $this->post,
                false,
                "删除对应关系失败"
            );
        }

    }

    public function deleteAllPID($sid){
        if(empty($sid)){
            return false;
        }
        $sql=sprintf("delete from %s where SID=%d",self::$table,$sid);
        return $this->pdo->query($sql);
    }

    public function deleteConnection($sid,$pid){
        if(empty($sid) || empty($pid)){
            return false;
        }
        $sql=sprintf("delete from %s where SID=%d and PID=%d;",self::$table,$sid,$pid);
        $this->pdo->query($sql);
        return !$this->checkConnection($sid,$pid);
    }

    public function checkConnection($sid,$pid){
        if(empty($sid) || empty($pid)){
            return false;
        }
        $sql=sprintf("select * from %s where SID=%d and PID=%d",self::$table,$sid,$pid);
        return $this->pdo->getFirstRow($sql);
    }

    public function createNewConnection($sid,$pid){
        if(empty($sid) || empty($pid)){
            return false;
        }
        $data=[
            'SID'=>$sid,
            'PID'=>$pid
        ];
        $this->handleSql($data,0);
        return true;
    }
}