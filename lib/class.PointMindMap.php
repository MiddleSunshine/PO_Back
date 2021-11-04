<?php

class PointMindMap extends Base {
    public $pointsConnection;
    public $point;

    public function __construct($get = [], $post = [])
    {
        parent::__construct($get, $post);
        static::$table=Points::$table;
        $this->pointsConnection=new PointsConnection();
        $this->point=new Points();
    }

    public function getAllSubPointID($pid,&$returnData){
        // subPids
        $subPids=$this->pointsConnection->getSubParentId($pid);
        if (empty($subPids)){
            return [];
        }
        foreach ($subPids as $subPid){
            !isset($returnData[$subPid]) && $returnData[$subPid]=[];
            $returnData[$subPid][]=$this->getAllSubPointID($subPid,$returnData[$subPid]);
        }
        return $returnData;
    }

    public function getAllParentPointID($subId,&$returnData){
        
    }
}