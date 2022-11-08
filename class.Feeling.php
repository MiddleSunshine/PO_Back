<?php

class Feeling extends Base{
    public function Save(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['Type'])){
            return self::returnActionResult($this->post,false,'Please select the mode');
        }
        $this->handleSql($this->post,0);
        return self::returnActionResult($this->post);
    }

    public function preSave()
    {
        $this->post['LastUpdateTime']=date("Y-m-d H:i:s");
    }

    public function List(){

    }
}