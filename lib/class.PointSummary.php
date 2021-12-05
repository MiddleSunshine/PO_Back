<?php

class PointSummary extends Base{
    public static $table="Point_Summary";

    public function Detail()
    {
        $id=$this->get['ID'] ?? 0;
        if(empty($id)){
            return self::returnActionResult(
                $this->get,
                false,
                "参数错误"
            );
        }
        $sql=sprintf("select * from %s where ID=%d;",static::$table,$id);
        $pointSummary=$this->pdo->getFirstRow($sql);
        if(!empty($pointSummary['file'])){
            $pointSummary['file_content']=file_get_contents(SummaryFilePath.DIRECTORY_SEPARATOR.$pointSummary['file'].".md");
        }else{
            $pointSummary['file_content']="";
        }
        return self::returnActionResult(
            [
                'PointSummary'=>$pointSummary
            ]
        );
    }

    public function NewPointSummary(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['Title'])){
            return self::returnActionResult(
                $this->post,
                false,
                "Please Input Title"
            );
        }
        $this->post['AddTime']=date("Y-m-d H:i:s");
        $this->post['LastUpdateTime']=date("Y-m-d H:i:s");
        if(!empty($this->post['file_content'])){
            file_put_contents(SummaryFilePath.DIRECTORY_SEPARATOR.$this->post['Title'].".md",$this->post['file_content']);
        }
        $this->handleSql($this->post,0);

        return self::returnActionResult();
    }

    public function preSave()
    {
        if(!empty($this->post['file_content']) && !empty($this->post['Title'])){
            file_put_contents(SummaryFilePath.DIRECTORY_SEPARATOR.$this->post['Title'].".md",$this->post['file_content']);
        }
        $this->post['LastUpdateTime']=date("Y-m-d H:i:s");
    }

}