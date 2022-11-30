<?php

class SearchResult{
    public $data=[];
    public $id='';
    public function __construct($item,$id)
    {
        $this->id=$id;
        $this->data=$item;
    }

    public function setHighLight($content){
        $this->data['Highlight']=$content;
    }
}