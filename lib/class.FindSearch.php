<?php

class FindSearch extends ElasticSearch{
    public $storeDataFilePath;
    public $tempStoreResult;
    public function __construct()
    {
        $this->storeDataFilePath=MD_FILE_INDEX;
        if (!is_dir($this->storeDataFilePath)){
            mkdir($this->storeDataFilePath);
            chmod($this->storeDataFilePath,0777);
        }
        $this->tempStoreResult=INDEX_FILE.DIRECTORY_SEPARATOR."findResult.txt";
    }

    public function NewDatabase($databseName)
    {
        return true;
    }

    public function StoreDocument($index, $ID, $storeData)
    {
        $storeDataDir=$this->storeDataFilePath.$ID.DIRECTORY_SEPARATOR;
        if (!is_dir($storeDataDir)){
            mkdir($storeDataDir);
            chmod($storeDataDir,0777);
        }
        file_put_contents($storeDataDir."FindSearch_setting",json_encode($storeData));
        return true;
    }

    public function SearchMultipleFileds($index, $search, $source = 'ID')
    {
        if (strpos($search,' ')!==false){
            $searchs=explode(' ',$search);
            $returnData=[];
            foreach ($searchs as $search){
                $returnData=array_merge($returnData,$this->SearchMultipleFileds($index,$search,$source));
            }
            return $returnData;
        }
        $seprate="'";
        if (strpos($search,'"')!==false){
            $seprate="'";
        }
        if (strpos($search,"'")!==false){
            $seprate='"';
        }
        if (strpos($search,'"')!==false && strpos($search,"'")!==false){
            $seprate='';
        }
        $cmd=sprintf("grep -irR %s%s%s %s* > %s",$seprate,$search,$seprate,$this->storeDataFilePath,$this->tempStoreResult);
        exec($cmd);
        exec(sprintf("echo '%s' >> %s",$cmd,$this->tempStoreResult));
        $searchResult=file_get_contents($this->tempStoreResult);
        $returnData=[];
        foreach (explode(PHP_EOL,$searchResult) as $item){
            $searchEachPart=explode(':',$item);
            $fileName=$searchEachPart[0];
            unset($searchEachPart[0]);
            $highLisght=implode(':',$searchEachPart);
            $fileName=explode(DIRECTORY_SEPARATOR,$fileName);
            $pid=$fileName[count($fileName)-2];
            if (empty($pid)){
                continue;
            }
            $searchResultInstance=new SearchResult([],$pid);
            // fixme 这里返回的数据太大了，所以没有什么意义。加一点解析的代码
            $highLisght='';
            $searchResultInstance->setHighLight('markdown_content',$highLisght);
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