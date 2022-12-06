<?php

class SameKeyword extends Base{

    protected $doNotCheckLogin=true;

    public static $table='Same_Point_Keyword';
    public function isSameKeyword($keyword){
        if (empty($keyword)){
            return [$keyword];
        }
        if (str_contains($keyword,'"') && str_contains($keyword,"'")){
            return [$keyword];
        }
        $outside="'";
        if (str_contains($keyword,"'")){
            $outside='"';
        }
        if ($outside=='"'){
            $sql='select * from '.static::$table.' where keywords like "%{$keyword}%";';
        }else{
            $sql="select * from ".static::$table." where keywords like '%{$keyword}%';";
        }
        $sameKeywords=$this->pdo->getRows($sql);
        if (empty($sameKeywords)){
            return [$keyword];
        }
        $returnData=[];
        foreach ($sameKeywords as $keywords){
            $keywords=json_decode($keywords['keywords'],1);
            $returnData=array_merge($keywords,$returnData);
        }
        return $returnData;
    }
}