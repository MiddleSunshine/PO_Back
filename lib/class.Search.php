<?php

class Search extends Base{
    /**
     * @var $elasticSearch ElasticSearch
     */
    public $elasticSearch;
    private $elasticSearchIndex="po";
    protected $error;
    private $todoQueue;
    private $failQueue;
    private $sync_queue_index;

    const MD_FILE_KEY='markdown_content';

    const OPTION_SAVE="SAVE";
    const OPTION_DELETE='Delete';

    public function syncData($limit=2000){
        try {
            $this->preCheck();
        }catch (\Exception $e){
            $this->error=$e->getMessage();
            return false;
        }
        $files=scandir($this->todoQueue);
        unset($files[0]);
        unset($files[1]);
        $files=array_slice($files,0,$limit);
        $sql=sprintf("select * from %s where ID in (%s);",Points::$table,implode(",",$files));
        $points=$this->pdo->getRows($sql,'ID');
        foreach ($files as $file){
            $option=file_get_contents($this->todoQueue.$file);
            unlink($this->todoQueue.$file);
            if (isset($points[$file])){
                switch ($option){
                    case self::OPTION_SAVE:
                        $storeData=$points[$file];
                        $storeData[self::MD_FILE_KEY]="";
                        if (!empty($storeData['file'])){
                            $storeData[self::MD_FILE_KEY]=File::getFileContent($storeData['ID'],$storeData['file']);
                        }
                        if(!$this->elasticSearch->StoreDocument($this->elasticSearchIndex,$file,$storeData)){
                            file_put_contents($this->failQueue.$file,date("Y-m-d H:i:s")." / ".$this->elasticSearch->getError().PHP_EOL,FILE_APPEND);
                        }
                        break;
                    case self::OPTION_DELETE:
                        $this->elasticSearch->DeleteDocument($this->elasticSearchIndex,$file);
                        break;
                }
            }else{
                // 这个数据在数据库中已经不存在了
                $this->elasticSearch->DeleteDocument($this->elasticSearchIndex,$file);
            }
        }
        return true;
    }

    public function addQueue($ID,$option=self::OPTION_SAVE){
        try {
            $this->preCheck();
        }catch (\Exception $e){
            $this->error=$e->getMessage();
            return false;
        }
        file_put_contents($this->todoQueue.$ID,$option);
        return true;
    }

    public function refreshESAllData(){
        $page=0;
        $pageSize=1000;
        $this->elasticSearch->NewDatabase($this->elasticSearchIndex);
        while ($page<10000){
            $page++;
            $sql=sprintf("select ID from %s limit %d,%d;",Points::$table,($page-1)*$pageSize,$pageSize);
            $data=$this->pdo->getRows($sql);
            if (empty($data)){
                break;
            }
            foreach ($data as $item){
                $this->error=$this->addQueue($item['ID']);
                if (!$this->error){
                    break;
                }
            }
        }
        return empty($this->error);
    }

    public function getError(){
        return $this->error;
    }

    public function preCheck(){
        // 采用文件目录的形式实现队列管理
        $this->sync_queue_index=INDEX_FILE.DIRECTORY_SEPARATOR."SYNC_QUEUES".DIRECTORY_SEPARATOR;
        if (!is_dir($this->sync_queue_index)){
            mkdir($this->sync_queue_index);
        }
        $this->todoQueue=$this->sync_queue_index."todo".DIRECTORY_SEPARATOR;
        $this->failQueue=$this->sync_queue_index."fail".DIRECTORY_SEPARATOR;
        if (!is_dir($this->todoQueue)){
            mkdir($this->todoQueue);
        }
        if (!is_dir($this->failQueue)){
            mkdir($this->failQueue);
        }
        // 初始化elaticsearch
        $this->elasticSearch=new ElasticSearch();
    }
}