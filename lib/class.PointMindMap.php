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
            return true;
        }
        foreach ($subPids as $subPid){
            !isset($returnData[$subPid]) && $returnData[$subPid]=[];
            $this->getAllSubPointID($subPid,$returnData[$subPid]);
        }
    }

    public function getAllParentPointID($subId,&$returnData){
        // parent id
        $parentIds=$this->pointsConnection->getParentId($subId);
        if (empty($parentIds)){
            return true;
        }
        foreach ($parentIds as $parentId){
            if ($parentId==0){
                continue;
            }
            !isset($returnData[$parentId]) && $returnData[$parentId]=[];
            $this->getAllParentPointID($parentId,$returnData[$parentId]);
        }
    }
}