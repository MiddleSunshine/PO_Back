<?php

class ElasticSearch{
    public $ES_Service_URL='';

    /**
     * @var HttpCrawler
     */
    public $crawler;
    protected $error;

    public function __construct()
    {
        $this->crawler=new HttpCrawler();
        if (!defined('ES_SERVER')){
            throw new Exception("Can't find the elasticsearch service");
        }
        if (empty(ES_SERVER)){
            throw new Exception("elasticsearch not setting!");
        }
        $this->ES_Service_URL=ES_SERVER;
    }

    public function NewDatabase($databseName){
        $storeDatabaseName=strtolower($databseName);
        if ($storeDatabaseName!==$databseName){
            $this->error="索引名必须是小写的";
            return false;
        }
        $response=$this->crawler->GetHttpResult(
            sprintf("%s/%s",$this->ES_Service_URL,$databseName),
            [
                'customer_method'=>'PUT'
            ]
        );
        if ($response['code']==200){
            return true;
        }
        $content=json_decode($response['content'],1);
        $this->error=$content['error']['reason'];
        return false;
    }

    public function StoreDocument($index,$ID,$storeData){
        $response=$this->crawler->GetHttpResult(
            sprintf("%s/%s/_doc/%s",$this->ES_Service_URL,$index,$ID),
            [
                'method'=>'post',
                'postdata'=>json_encode($storeData),
                'addheader'=>[
                    'Content-Type: application/json'
                ],
                'customer_method'=>'put'
            ]
        );
        if ($response['code']!=201 && $response['code']!=200){
            $content=json_decode($response['content'],1);
            $this->error=$content['error'];
            return false;
        }
        return true;
    }

    public function DeleteDocument($index,$ID){
        $response=$this->crawler->GetHttpResult(
            sprintf("%s/%s/_doc/%s",$this->ES_Service_URL,$index,$ID),
            [
                'customer_method'=>'DELETE'
            ]
        );
        if ($response['code']!==200){
            $content=json_decode($response['content'],1);
            $this->error=$content['error'];
            return false;
        }
        return true;
    }

    public function getError(){
        return $this->error;
    }
}

