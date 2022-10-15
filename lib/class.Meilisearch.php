<?php
require_once __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."Worker".DIRECTORY_SEPARATOR."vendor".DIRECTORY_SEPARATOR."autoload.php";
use MeiliSearch\Client;

class Meilisearch extends ElasticSearch {
    /**
     * @var Client
     */
    public $client;
    public function __construct()
    {
        $this->client=new Client(ES_SERVER);
    }

    public function NewDatabase($databseName)
    {
        return true;
    }

    public function StoreDocument($index, $ID, $storeData)
    {
        $this->client->index($index)->addDocuments($storeData);
        return true;
    }

    public function DeleteDocument($index, $ID)
    {
        // todo 这里找一下文档内容
    }

    public function SearchMultipleFileds($index, $search, $source = 'ID')
    {
        $searchResult=$this->client->index($index)->search($search);
        $returnData=[];
        foreach ($searchResult->getHits() as $item){
            if (empty($item['ID'])){
                continue;
            }
            $returnData[]=new SearchResult($item,$item['ID']);
        }
        return $returnData;
    }

    public function SearchOneField($index, $field, $keyword, $source = 'ID')
    {
        return false;
    }
}