<?php

class GTDLabelConnection extends Base{
    public static $table="GTD_Label_Connection";

    public function GetGTDLabel(){
        $GTDID=$this->get['ID'] ?? 0;
        if (empty($GTDID)){
            return self::returnActionResult(
                $this->get,
                false,
                "参数错误"
            );
        }
        return self::returnActionResult(
            [
                'Connection'=>$this->getLabels($GTDID)
            ]
        );
    }

    public function UpdateConnection(){
        $this->post=json_decode($this->post,1);
        $GTDID=$this->post['GTD_ID'] ?? 0;
        $LabelIDs=$this->post['Label_ID'] ?? [];
        if (empty($GTDID) || empty($LabelIDs)){
            return self::returnActionResult(
                $this->post,
                false,
                "参数错误"
            );
        }
        $labels=$this->getLabels($GTDID,0,'Label_ID');
        $insert=[];
        foreach ($LabelIDs as $labelID){
            if (!isset($labels[$labelID])){
                // insert 部分
                $insert[]=$labelID;
            }else{
                // 相同的数据，不需要更新
                unset($labels[$labelID]);
            }
        }
        $deleted=array_keys($labels);
        if (!empty($insert)){
            foreach ($insert as $labelId){
                $this->handleSql([
                    'GTD_ID'=>$GTDID,
                    'Label_ID'=>$labelId
            ],0);
            }
        }
        if (!empty($deleted)){
            $sql=sprintf("delete from %s where Label_ID in (%s)",static::$table,implode(",",$deleted));
            $this->pdo->query($sql);
        }
        return self::returnActionResult(
            [
                'post'=>$this->post,
                'insert'=>$insert,
                'delete'=>$deleted
            ]
        );
    }

    public function DeleteConnection(){
        $GTD_ID=$this->get['GTD_ID'] ?? 0;
        $Label_ID=$this->get['Label_ID'] ?? 0;
        if (empty($GTD_ID) || empty($Label_ID)){
            return self::returnActionResult(
                $this->get,
                false,
                "参数错误"
            );
        }
        $sql=sprintf("delete from %s where GTD_ID=%d and Label_ID=%d",static::$table,$GTD_ID,$Label_ID);
        $this->pdo->query($sql);
        return self::returnActionResult(
            [
                'sql'=>$sql
            ]
        );
    }

    public function getLabels($GTD_ID=0,$Label_ID=0,$keyName=''){
        if (empty($GTD_ID) && empty($Label_ID)){
            return false;
        }
        if ($GTD_ID){
            $where=sprintf("where GTD_ID=%d",$GTD_ID);
        }else{
            $where=sprintf("where Label_ID=%d",$Label_ID);
        }
        $sql=sprintf("select * from %s %s",static::$table,$where);
        return $this->pdo->getRows($sql,$keyName);
    }
}