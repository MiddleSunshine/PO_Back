<?php

class PointSummary extends Base{
    public static $table="Point_Summary";

    public function List()
    {
        $sql=sprintf("select * from %s order by ID desc;",static::$table);
        $summaries=$this->pdo->getRows($sql);
        $pointTagConnection=new PointTagConnection();
        foreach ($summaries as &$summary){
            $tagIds=$pointTagConnection->getSummaryTagConnection($summary['ID']);
            if (!empty($tagIds)){
                $sql=sprintf("select * from %s where ID in (%s) and Deleted=0;",PointTag::$table,implode(",",$tagIds));
                $summary['tags']=$this->pdo->getRows($sql);
            }else{
                $summary['tags']=[];
            }
        }
        return self::returnActionResult(
            [
                'summaries'=>$summaries
            ]
        );
    }

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
            $filePath=SummaryFilePath.DIRECTORY_SEPARATOR.$pointSummary['file'].".md";
            $pointSummary['file_content']=file_exists($filePath)?file_get_contents($filePath):"";
            $pointSummary['filePath']=$filePath;
        }else{
            $pointSummary['filePath']="";
            $pointSummary['file_content']="";
        }
        $pointTagConnection=new PointTagConnection();
        $pointSummary['tag_ids']=$pointTagConnection->getSummaryTagConnection($id);;
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
            file_put_contents(SummaryFilePath.DIRECTORY_SEPARATOR.$this->post['file'].".md",$this->post['file_content']);
        }
        $data=$this->handleSql($this->post,0,'',true);
        $pointTagConnection=new PointTagConnection();
        if (!$pointTagConnection->updateConnection($data['Data']['ID'],$this->post['tag_ids'])){
            return self::returnActionResult(
                $this->post,
                false,
                "New Point Summary Error"
            );
        }
        return self::returnActionResult();
    }

    public function preSave()
    {
        if(!empty($this->post['file_content']) && !empty($this->post['file'])){
            file_put_contents(SummaryFilePath.DIRECTORY_SEPARATOR.$this->post['file'].".md",$this->post['file_content']);
        }
        $this->post['LastUpdateTime']=date("Y-m-d H:i:s");
        $pointTagConnection=new PointTagConnection();
        $pointTagConnection->updateConnection($this->post['ID'],$this->post['tag_ids']);
    }

}