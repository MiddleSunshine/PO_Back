<?php

class PointTag extends Base{
    public static $table="Point_Tag";

    public function NewTag(){
        $this->post=json_decode($this->post,1);
        if(empty($this->post['Tag'])){
            return self::returnActionResult(
                $this->post,
                false,
                "参数错误"
            );
        }
    }
}