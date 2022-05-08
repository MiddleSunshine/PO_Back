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

    public function GetAllDatabase(){
        // todo 不要使用这个API，换一个
        $response=$this->crawler->GetHttpResult(
            sprintf("%s/_cat/indices?v",$this->ES_Service_URL)
        );
        $databses=explode(PHP_EOL,$response['content']);
        $returnData=[];
        foreach ($databses as $database){
            $data=explode("\t",$database);
            $s=1;
            // green  open   .geoip_databases eXUrExHzScWaU2c_MKm7Eg   1   0         40            0       38mb           38mb
        }
    }

    public function StoreDocument($index,$ID,$storeData){
        $response=$this->crawler->GetHttpResult(
            sprintf("%s/%s/_doc/%s",$this->ES_Service_URL,$index,$ID),
            [
                'method'=>'post',
                'postdata'=>json_encode($storeData),
                'addheader'=>[
                    'Content-Type: application/json'
                ]
            ]
        );
        if ($response['code']!=201 && $response['code']!=200){
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

