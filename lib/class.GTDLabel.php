<?php

class GTDLabel extends Base{
    public static $table="GTDLabel";

    public function getUsefulLabel(){
        $sql=sprintf("select * from %s where Deleted=0",static::$table);
        return $this->pdo->getRows($sql,'ID');
    }
}