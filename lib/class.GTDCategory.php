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
}