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
        $pids=[$startID];
        $endlessPrevent=0;
        $prePid=-1;
        while (!empty($pids) && $endlessPrevent<1000){
            $endlessPrevent++;
            foreach ($pids as $pid){
                $subIds=$this->getSubParentId($pid);
                if (in_array($subPID,$subIds)){
                    $prePid=$pid;
                    break;
                }
            }
        }
        if ($prePid<0){
            return self::returnActionResult(
                $this->get,
                false,
                "Data Wrong!"
            );
        }
        $this->deleteConnection($prePid,$subPID);
        $this->updatePointsConnection($PID,$subPID);
        return self::returnActionResult();
    }

    public function Update(){
        $PID=$this->get['PID'] ?? -1;
        $subPID=$this->get['SubPID'] ?? -1;
        if ($PID<0 || $subPID<0){
            return self::returnActionResult($this->get,false,"参数错误");
        }
        $this->updatePointsConnection($PID,$subPID);
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

    public function deleteConnection($PID,$subPID){
        $sql=sprintf("delete from %s where PID=%d and SubPID=%d;",static::$table,$PID,$subPID);
        $this->pdo->query($sql);
        $sql=sprintf("select * from %s where SubPID=%d;",static::$table,$subPID);
        $connection=$this->pdo->getFirstRow($sql);
        if (empty($connection)){
            $sql=sprintf("update %s set status='%s' where ID=%d",Points::$table,Points::STATUS_INIT,$subPID);
            $this->pdo->query($sql);
        }
    }

    public function getSubParentId($pid){
        $sql=sprintf("select SubPID from %s where PID=%d;",static::$table,$pid);
        $dataBaseData=$this->pdo->getRows($sql);
        return array_column($dataBaseData,'SubPID');
    }

    public function getParentId($id){
        $sql=sprintf("select PID from %s where SubPID=%d;",static::$table,$id);
        $databaseData=$this->pdo->getRows($sql);
        return array_column($databaseData,'PID');
    }

    public function updatePointsConnection($pid,$subPID){
        $sql=sprintf("select * from %s where PID=%d and SubPID=%d",static::$table,$pid,$subPID);
        $connection=$this->pdo->getFirstRow($sql);
        if ($connection){
            return true;
        }
        $sql=sprintf("insert into %s(PID,SubPID) value (%d,%d);",static::$table,$pid,$subPID);
        return $this->pdo->query($sql);
    }
}