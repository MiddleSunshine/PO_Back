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
        $status=$this->get['Status'] ?? self::STATUS_ACTIVE;
        if (empty($this->get['PID'])){
            $where='';
        }else{
            $where='and PID='.$this->get['PID'];
        }
        $sql=sprintf("select * from %s where Status='%s' %s order by ID desc;",self::$table,$status,$where);
        $scares=$this->pdo->getRows($sql);
        $scareExplain=new Explain();
        foreach ($scares as $scare){
            $scare['Explains']=$scareExplain->getExplains($scare['ID']);
        }
        return self::returnActionResult([
            'ScareList'=>$scares
        ]);
    }

    public function NewScare(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['Scare'])){
            return self::returnActionResult($this->post,false,"Please input the scare");
        }
        empty($this->post['Status']) && $this->post['Status']=self::STATUS_ACTIVE;
        empty($this->post['PID']) && $this->post['PID']=0;
        $this->post['AddTime']=date("Y-m-d H:i:s");
        $this->post['LastUpdateTime']=date("Y-m-d H:i:s");
        $this->handleSql($this->post,0);
        return self::returnActionResult();
    }
}