<?php

class PointTag extends Base{
    public static $table="Point_Tag";

    public function List()
    {
        $sql=sprintf("select * from %s where Deleted=0 order by ID desc;",static::$table);
        return self::returnActionResult(
            $this->pdo->getRows($sql)
        );
    }

    public function NewTag(){
        $this->post=json_decode($this->post,1);
        if(empty($this->post['Tag'])){
            return self::returnActionResult(
                $this->post,
                false,
                "参数错误"
            );
        }
        $this->handleSql($this->post,0);
        return self::returnActionResult();
    }
}