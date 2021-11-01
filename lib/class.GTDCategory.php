<?php

class GTDCategory extends Base{
    public static $table="GTD_Category";

    const START_PROCESSING='Processing';
    const START_ARCHIVED='Archived';

    public function getProcessingCategory($checkStartTime=true){
        $sql=sprintf("select * from %s where Status='%s'",static::$table,self::START_PROCESSING);
        $Categories=$this->pdo->getRows($sql);
        if ($checkStartTime){
            $now=time();
            foreach ($Categories as $index=>$category){
                if (empty($category['StartTime'])){
                    continue;
                }
                $startTime=strtotime($category['StartTime']);
                if($startTime>$now) unset($Categories[$index]);
            }
        }
        return $Categories;
    }

    public function NewCategory(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['Category'])){
            return self::returnActionResult($this->post,false,"缺少参数");
        }
        $this->post['Status']=self::START_PROCESSING;
        $this->handleSql($this->post,0);
        return self::returnActionResult();
    }

    public function UpdateCategory(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['ID'])){
            return self::returnActionResult(
                $this->post,
                false,
                "参数错误"
            );
        }
        $this->handleSql($this->post,$this->post['ID'],'');
        return self::returnActionResult();
    }
}