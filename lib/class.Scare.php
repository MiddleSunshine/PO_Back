<?php

class Scare extends Base{
    public static $table="Scare";

    const STATUS_ACTIVE='Active';
    const STATUS_INACTIVE='Inactive';

    public function AllScare(){
        $sql=sprintf("select * from %s order by ID desc;",self::$table);
        $scares=$this->pdo->getRows($sql);
        return self::returnActionResult([
            'Scares'=>$scares
        ]);
    }

    public function ScareList(){

    }

    public function NewScare(){

    }
}