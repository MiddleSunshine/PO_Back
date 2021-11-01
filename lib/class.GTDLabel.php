<?php

class GTDLabel extends Base{
    public static $table="GTDLabel";

    public function List()
    {
        return self::returnActionResult(
            [
                'List'=>array_values($this->getUsefulLabel())
            ]
        );
    }

    public function NewLabel(){
        $this->post=json_decode($this->post,1);
//        if (empty($this->post['Label']) || empty($this->post['Color'])){
//            return self::returnActionResult(
//                $this->post,
//                false,
//                "参数错误"
//            );
//        }
        $this->handleSql($this->post,0,'Label');
        return self::returnActionResult();
    }

    public function UpdateLabel(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['ID'])){
            return self::returnActionResult(
                $this->post,
                false,
                "参数错误"
            );
        }
        $this->handleSql($this->post,$this->post['ID']);
        return self::returnActionResult($this->post);
    }


    public function getUsefulLabel(){
        $sql=sprintf("select * from %s where Deleted=0",static::$table);
        return $this->pdo->getRows($sql,'ID');
    }
}