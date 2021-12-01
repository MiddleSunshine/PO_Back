<?php

class PointSummary extends Base{
    public static $table="Point_Summary";

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
        $this->handleSql($this->post,0);
        return self::returnActionResult();
    }

    public function preSave()
    {
        $this->post['LastUpdateTime']=date("Y-m-d H:i:s");
    }

}