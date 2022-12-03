<?php

class Explain extends Base{
    public static $table='Scare_Explain';

    public function ExplainList(){
        $scareID=$this->get['Scare_ID'] ?? 0;
        if (empty($scareID)){
            return self::returnActionResult($this->get,false,'Param Error');
        }
        $sql=sprintf("select * from %s where Scare_ID=%d order by ID desc;",self::$table,$scareID);
        $explains=$this->pdo->getRows($sql);
        return self::returnActionResult(
            [
                'Explains'=>$explains
            ]
        );
    }

    public function NewExplain(){
        $this->post=json_decode($this->post,1);
        if(empty($this->post['Explain'])){
            return self::returnActionResult($this->post,false,'Please Input The Explain');
        }
        $this->post['AddTime']=date("Y-m-d H:i:s");
        $this->post['LastUpdateTime']=date("Y-m-d H:i:s");
        $this->handleSql($this->post,0);
        return self::returnActionResult();
    }

    public function getExplains($scareId){
        if (empty($scareId)){
            return [];
        }
        $sql=sprintf("select * from %s where Scare_ID=%d;",self::$table,$scareId);
        return $this->pdo->getRows($sql);
    }
}