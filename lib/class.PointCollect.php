<?php

class PointTemplate{
    public $point;
    public $createTimestamp;

    const POINT_KEY='point';
    const CREATE_TIME_KEY='createtime';

    public function __construct($data)
    {
        $this->point=$data[self::POINT_KEY] ?? "";
        $this->createTimestamp=$data[self::CREATE_TIME_KEY] ?? time();
    }

    public function output(){
        return [
            self::POINT_KEY=>$this->point,
            self::CREATE_TIME_KEY=>$this->createTimestamp
        ];
    }
}

class Collector{
    public $fileName;
    public $points=[];
    public $uniquePoint=[];
    public function __construct($fileName)
    {
        $storePath=POINT_COLLECT_INDEX.$fileName;
        if (!file_exists($storePath)){
            touch($storePath);
        }
        $this->fileName=$fileName;
        $content=file_get_contents($storePath);
        $points=json_decode($content,1);
        if (!empty($points)){
            foreach ($points as $point){
                $instance=new PointTemplate($point);
                $this->uniquePoint[$instance->point]=$instance->createTimestamp;
                $this->points[$instance->createTimestamp]=$instance;
            }
        }
        ksort($this->points);
    }

    public function newPoint($point){
        if (isset($this->uniquePoint[$point])){
            return false;
        }
        $createTime=time();
        $this->points[$createTime]=new PointTemplate([
            PointTemplate::POINT_KEY=>$point,
            PointTemplate::CREATE_TIME_KEY=>$createTime
        ]);
        $this->SaveCollect();
    }

    public function updatePoint($createTime,$point){
        if (isset($this->uniquePoint[$point])){
            return false;
        }
        /**
         * @var $instance PointTemplate
         */
        $instance=$this->points[$createTime] ?? false;
        if ($instance){
            $instance->point=$point;
        }
        $this->points[$createTime]=$instance;
        $this->SaveCollect();
    }

    public function deletePoint($createTime){
        unset($this->points[$createTime]);
        $this->SaveCollect();
    }

    public function RemoveCollect(){
        unlink(POINT_COLLECT_INDEX.$this->fileName);
    }

    public function SaveCollect(){
        ksort($this->points);
        $points=[];
        foreach ($this->points as $point){
            /**
             * @var $point PointTemplate
             */
            $points[]=$point->output();
        }
        file_put_contents(
            POINT_COLLECT_INDEX.$this->fileName,
            json_encode($points)
        );
    }

    public static function Collectors(){
        $files=scandir(POINT_COLLECT_INDEX);
        unset($files[0]);
        unset($files[1]);
        $returnData=[];
        foreach ($files as $file){
            $returnData[$file]=new self($file);
        }
        return $returnData;
    }
}

class PointCollect extends Base{
    public function NewCollector(){
        $file_name=$this->get['PID'] ?? 0;
        if ($file_name!=0 && empty($file_name)){
            return self::returnActionResult($this->get,false,"Empty Data");
        }
        new Collector($file_name);
        return self::returnActionResult();
    }

    public function CollectList(){
        $pid=$this->get['PID'] ?? 0;
        $collectors=Collector::Collectors();
        $points=[];
        if (!empty($collectors)){
            $sql=sprintf("select * from %s where ID in (%s);",Points::$table,implode(",",array_keys($collectors)));
            $points=$this->pdo->getRows($sql,'ID');
        }
        $collectorsReturnData=[];
        $pointTemplates=[];
        $index=-1;
        foreach ($collectors as $collector){
            $index++;
            $pointDatabase=isset($points[$collector->fileName])?$points[$collector->fileName]:false;
            $collectorsReturnData[$index]=[
                'label'=>$pointDatabase?$pointDatabase['keyword']:"No Name",
                'ID'=>$collector->fileName,
                'points'=>[]
            ];
            /**
             * @var $collector Collector
             */
            foreach ($collector->points as $point){
                /**
                 * @var $point PointTemplate
                 */
                $collectorsReturnData[$index]['points'][]=$point->output();
            }
            if ($collector->fileName==$pid){
                $pointTemplates=$collectorsReturnData[$index]['points'];
            }
        }
        return self::returnActionResult(
            [
                'Collector'=>$collectorsReturnData,
                'Points'=>$pointTemplates
            ]
        );
    }

    public function NewPoint(){
        $this->post=json_decode($this->post,1);
        $fileName=$this->post['PID'] ?? "";
        $point=$this->post['point'] ?? "";
        if (($fileName!=0 && empty($fileName)) || empty($point)){
            return self::returnActionResult($this->post,false,"Data Error");
        }
        $collector=new Collector($fileName);
        $collector->newPoint($point);
        return self::returnActionResult();
    }

    public function UpdatePoint(){
        $this->post=json_decode($this->post,1);
        $fileName=$this->post['PID'] ?? "";
        $point=$this->post['point'] ?? "";
        $createTime=$this->post['create_time'] ?? "";
        if (($fileName!=0 && empty($fileName)) || empty($point) || empty($createTime)){
            return self::returnActionResult($this->post,false,"Data Error");
        }
        $collector=new Collector($fileName);
        $collector->updatePoint($createTime,$point);
        return self::returnActionResult();
    }

    public function DeletePoint(){
        $this->post=json_decode($this->post,1);
        $fileName=$this->post['PID'] ?? "";
        $createTime=$this->post['create_time'] ?? "";
        if (empty($createTime)){
            return self::returnActionResult($this->get,false,"Empty Data");
        }
        $collector=new Collector($fileName);
        $collector->deletePoint($createTime);
        return self::returnActionResult();
    }

    public function DeleteCollector(){
        $fileName=$this->get['PID'] ?? "";
        if ($fileName!=0 && empty($fileName)){
            return self::returnActionResult($this->post,false,'Empty Data');
        }
        $collector=new Collector($fileName);
        $collector->RemoveCollect();
        return self::returnActionResult();
    }
}