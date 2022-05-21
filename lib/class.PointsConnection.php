<?php

class PointsConnection extends Base{
    public static $table="Points_Connection";

    public function NewConnection(){
        $startID=$this->get['StartID'] ?? -1;
        $PID=$this->get['PID'] ?? -1;
        $subPID=$this->get['SubPID'] ?? -1;
        if ($PID<0 || $subPID<0 || $startID<0){
            return self::returnActionResult($this->get,false,"参数错误");
        }
        $prePid=$this->findPrePid($startID,$subPID);
        if ($prePid<0){
            return self::returnActionResult($this->get,false,"参数错误");
        }
        $this->deleteConnection($prePid,$subPID,false);
        $this->updatePointsConnection($PID,$subPID);
        return self::returnActionResult();
    }

    public function Update(){
        $PID=$this->get['PID'] ?? -1;
        $subPID=$this->get['SubPID'] ?? -1;
        $this->post=json_decode($this->post,1);
        if ($PID<0 || $subPID<0){
            return self::returnActionResult($this->get,false,"参数错误");
        }
        $this->updatePointsConnection($PID,$subPID,$this->post['note']);
        return self::returnActionResult($this->get);
    }

    public function Deleted(){
        $PID=$this->get['PID'] ?? -1;
        $subPID=$this->get['SubPID'] ?? -1;
        if ($PID<0 || $subPID<0){
            return self::returnActionResult($this->get,false,"参数错误");
        }
        $this->deleteConnection($PID,$subPID);
        return self::returnActionResult($this->post);
    }

    public function findPrePid($pid,$findSubID){
        $subIds=$this->getSubParentId($pid);
        $prePid=-1;
        foreach ($subIds as $subId){
            if ($subId==$findSubID){
                $prePid=$pid;
                break;
            }else{
                $prePid=$this->findPrePid($subId,$findSubID);
                if ($prePid>=0){
                    break;
                }
            }
        }
        return $prePid;
    }

    public function deleteConnection($PID,$subPID,$updatePoint=true){
        $sql=sprintf("delete from %s where PID=%d and SubPID=%d;",static::$table,$PID,$subPID);
        $this->pdo->query($sql);
//        $sql=sprintf("select * from %s where SubPID=%d;",static::$table,$subPID);
//        $connection=$this->pdo->getFirstRow($sql);
//        if (empty($connection) && $updatePoint){
//            $sql=sprintf("update %s set status='%s' where ID=%d",Points::$table,Points::STATUS_INIT,$subPID);
//            $this->pdo->query($sql);
//        }
    }

    public function getSubParentId($pid){
        $sql=sprintf("select SubPID from %s where PID=%d;",static::$table,$pid);
        $dataBaseData=$this->pdo->getRows($sql);
        return array_column($dataBaseData,'SubPID');
    }

    public function getSubPoints($pid,$returnNote=true){
        $sql=sprintf("select ID,SubPID,note from %s where PID=%d;",static::$table,$pid);
        return $this->pdo->getRows($sql);
    }

    public function getParentId($id){
        $sql=sprintf("select PID from %s where SubPID=%d;",static::$table,$id);
        $databaseData=$this->pdo->getRows($sql);
        return array_column($databaseData,'PID');
    }

    public function updatePointsConnection($pid,$subPID,$note=''){
        $sql=sprintf("select * from %s where PID=%d and SubPID=%d",static::$table,$pid,$subPID);
        $connection=$this->pdo->getFirstRow($sql);
        if ($connection){
            return true;
        }
        // 防止死循环
        $sql=sprintf("select * from %s where PID=%d and SubPID=%d",static::$table,$subPID,$pid);
        $connection=$this->pdo->getFirstRow($sql);
        if ($connection){
            return true;
        }
        $sql=sprintf("insert into %s(PID,SubPID,note) value (%d,%d,'%s');",static::$table,$pid,$subPID,$note);
        return $this->pdo->query($sql);
    }
}