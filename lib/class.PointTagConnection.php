<?php

class PointTagConnection extends Base{
    public static $table="PS_Tag_Connection";

    public function NewConnection(){
        $this->post=json_decode($this->post,true);
        if(empty($this->post['PS_ID']) || empty($this->post['TID'])){
            return self::returnActionResult($this->post,false,"参数错误");
        }
        if($this->checkConnection($this->post['PS_ID'],$this->post['TID'])){
            return self::returnActionResult(
                $this->post
            );
        }
        $this->createConnection($this->post['PS_ID'],$this->post['TID']);
        return self::returnActionResult();
    }

    public function DeleteConnection(){
        $this->post=json_decode($this->post,true);
        if(empty($this->post['PS_ID']) || empty($this->post['TID'])){
            return self::returnActionResult($this->post,false,"参数错误");
        }
        $sql=sprintf("delete from %s where PS_ID=%d and TID=%d;",static::$table,$this->post['PS_ID'],$this->post['TID']);
        $this->pdo->query($sql);
        if($this->checkConnection($this->post['PS_ID'],$this->post['TID'])){
            return self::returnActionResult(
                $this->post,
                false,
                "删除对应关系失败"
            );
        }
        return self::returnActionResult();
    }

    public function updateConnection($PSID,$tagsIds){
        if (empty($PSID)){
            return false;
        }
        if (empty($tagsIds)){
            $this->clearConnection($PSID);
            return true;
        }
        foreach ($tagsIds as $tagsId){
            if (!$this->checkConnection($PSID,$tagsId) && !empty($tagsId)){
                $this->createConnection($PSID,$tagsId);
            }
        }
        return true;
    }

    public function clearConnection($PSID){
        $sql=sprintf("delete from %s where PS_ID=%d;",static::$table,$PSID);
        $this->pdo->query($sql);
    }

    public function createConnection($PS_ID,$TID){
        if(empty($PS_ID) || empty($TID)){
            return false;
        }
        $data=[
            'PS_ID'=>$PS_ID,
            'TID'=>$TID
        ];
        $this->handleSql($data,0);
        return true;
    }

    public function checkConnection($PS_ID,$TID){
        if(empty($PS_ID) || empty($TID)){
            return false;
        }
        $sql=sprintf("select * from %s where PS_ID=%d and TID=%d",static::$table,$PS_ID,$TID);
        return $this->pdo->getFirstRow($sql);
    }

    public function getSummaryTagConnection($summaryId){
        $sql=sprintf("select * from %s where PS_ID=%d;",static::$table,$summaryId);
        $TagIds=$this->pdo->getRows($sql,'TID');
        return array_keys($TagIds);
    }
}