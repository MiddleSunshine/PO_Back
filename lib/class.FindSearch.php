<?php

class FindSearch extends ElasticSearch{
    public $storeDataFilePath;
    public function __construct()
    {
        $this->storeDataFilePath=MD_FILE_INDEX;
        if (!is_dir($this->storeDataFilePath)){
            mkdir($this->storeDataFilePath);
            chmod($this->storeDataFilePath,0777);
        }
    }

    public function NewDatabase($databseName)
    {
        return true;
    }

    public function StoreDocument($index, $ID, $storeData)
    {
        file_put_contents($this->storeDataFilePath.$ID.DIRECTORY_SEPARATOR."FindSearch_setting",json_encode($storeData));
        return true;
    }

    public function SearchMultipleFileds($index, $search, $source = 'ID')
    {
        $cmd=sprintf("grep -irR %s %s*",$search,$this->storeDataFilePath);
        $searchResult=exec($cmd);
        $returnData=[];
        foreach (explode(PHP_EOL,$searchResult) as $item){
            $searchEachPart=explode(':',$item);
            $fileName=$searchEachPart[0];
            unset($searchEachPart[0]);
            $highLisght=implode(':',$searchEachPart);
            $fileName=explode(DIRECTORY_SEPARATOR,$fileName);
            $pid=$fileName[count($fileName)-2];
            $searchResultInstance=new SearchResult([],$pid);
            $searchResultInstance->setHighLight($highLisght);
            $returnData[]=$searchResultInstance;
        }
        return $returnData;
    }

    public function SearchOneField($index, $field, $keyword, $source = 'ID')
    {
        return false;
    }

    public function DeleteDocument($index, $ID)
    {
        return false;
    }
}