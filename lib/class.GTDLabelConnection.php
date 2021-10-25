<?php

class GTDLabelConnection extends Base{
    public static $table="GTD_Label_Connection";

    public function getLabels($GTD_ID=0,$Label_ID=0){
        if (empty($GTD_ID) && empty($Label_ID)){
            return false;
        }
        if ($GTD_ID){
            $where=sprintf("where GTD_ID=%d",$GTD_ID);
        }else{
            $where=sprintf("where Label_ID=%d",$Label_ID);
        }
        $sql=sprintf("select * from %s %s",static::$table,$where);
        return $this->pdo->getRows($sql);
    }
}