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
}

class Collector{
    public $fileName;
    public $points=[];
    public $uniquePoint=[];
    public function __construct($fileName)
    {
        $fileName.=".json";
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
            $points[]=[
                PointTemplate::POINT_KEY=>$point->point,
                PointTemplate::CREATE_TIME_KEY=>$point->createTimestamp
            ];
        }
        file_put_contents(
            POINT_COLLECT_INDEX.$this->fileName,
            json_encode($points)
        );
    }
}

class PointCollect extends Base{
    public function NewPoint(){
        $this->post=json_decode($this->post,1);
        $fileName=$this->post['file_name'] ?? "";
        $point=$this->post['point'] ?? "";
        if (empty($fileName) || empty($point)){
            return self::returnActionResult($this->post,false,"Data Error");
        }
        $collector=new Collector($fileName);
        $collector->newPoint($point);
        return self::returnActionResult();
    }

    public function UpdatePoint(){
        $this->post=json_decode($this->post,1);
        $fileName=$this->post['file_name'] ?? "";
        $point=$this->post['point'] ?? "";
        $createTime=$this->post['create_time'] ?? "";
        if (empty($fileName) || empty($point) || empty($createTime)){
            return self::returnActionResult($this->post,false,"Data Error");
        }
        $collector=new Collector($fileName);
        $collector->updatePoint($createTime,$point);
        return self::returnActionResult();
    }

    public function DeletePoint(){
        $this->post=json_decode($this->post,1);
        $fileName=$this->post['file_name'] ?? "";
        $createTime=$this->post['create_time'] ?? "";
        if (empty($createTime)){
            return self::returnActionResult($this->get,false,"Empty Data");
        }
        $collector=new Collector($fileName);
        $collector->deletePoint($createTime);
        return self::returnActionResult();
    }

    public function DeleteCollector(){
        $this->post=json_decode($this->post,1);
        $fileName=$this->post['file_name'] ?? "";
        if (empty($fileName)){
            return self::returnActionResult($this->post,false,'Empty Data');
        }
        $collector=new Collector($fileName);
        $collector->RemoveCollect();
        return self::returnActionResult();
    }
}