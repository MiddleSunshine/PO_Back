<?php

class PointSummaryConnection extends Base{
    public static $table="PS_Connection";

    public function NewConnection(){
        $this->post=json_decode($this->post,1);
    }
}