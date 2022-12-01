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
        if (str_contains($search, '"')){
            $separate="'";
        }
        if (str_contains($search, "'")){
            $separate='"';
        }
        if (str_contains($search, '"') && str_contains($search, "'")){
            $separate='';
        }
        $cmd=sprintf("grep -irR %s%s%s %s* > %s",$separate,$search,$separate,$this->storeDataFilePath,$this->tempStoreResult);
        exec($cmd);
//        exec(sprintf("echo '%s' >> %s",$cmd,$this->tempStoreResult));
        $searchResult=file_get_contents($this->tempStoreResult);
        $returnData=[];
        $pids=[];
        foreach (explode(PHP_EOL,$searchResult) as $item){
            $searchEachPart=explode(':',$item);
            $fileName=$searchEachPart[0];
            unset($searchEachPart[0]);
            $highLight=implode(':',$searchEachPart);
            $fileName=explode(DIRECTORY_SEPARATOR,$fileName);
            $pid=$fileName[count($fileName)-2];
            if (isset($pids[$pid])){
                continue;
            }
            if (empty($pid)){
                continue;
            }
            $fName=$fileName[count($fileName)-1];
            switch ($fName){
                case "笔记.md":
                    $highLight=file_get_contents($searchResult[0]);
                    $pids[$pid]=1;
                    break;
                case 'MindNoteFile.json':
                    $highLight="Search Content In the WhiteBord";
                    $pids[$pid]=1;
                    break;
                default:
                    if (str_contains($fName,'json')){
                        $highLight="Search Content in the tldraw ({$fName})";
                        $pids[$pid]=1;
                    }else{
                        $highLight="Search Content In the Point";
                    }
            }
            $searchResultInstance=new SearchResult([],$pid);
            $searchResultInstance->setHighLight('markdown_content',$highLight);
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