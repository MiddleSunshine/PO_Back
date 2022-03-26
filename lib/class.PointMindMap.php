<?php
class PointMindMap extends Base {
    public $pointsConnection;
    public $point;

    public $parentPoints=[];
    public $parentPointsConnection=[];
    public $maxSubDeep=[];
    public $SubPointsAmount=0;
    public $ParentPointsAmount=0;

    public function __construct($get = [], $post = [])
    {
        parent::__construct($get, $post);
        static::$table=Points::$table;
        $this->pointsConnection=new PointsConnection();
        $this->point=new Points();
    }

    public function Index(){
        $id=$this->get['id'] ?? -1;
        if ($id<0){
            return self::returnActionResult($this->get,false,"Wrong Param");
        }
        $parentPoints=[];
        $this->getAllParentPointID($id,$parentPoints);
        unset($parentPoints);
        $returnData=[];
        $pointCommentInstance=new PointsComments();
        $outsideIndex=0;
        foreach ($this->parentPoints as $points){
            $returnData[$outsideIndex]=[];
            foreach ($points as $pointId){
                $returnData[$outsideIndex][]=[
                    'Point'=>$this->point->getPointDetail($pointId),
                    'Comments'=>$pointCommentInstance->getComments($pointId)
                ];
            }
            $outsideIndex++;
        }
        return self::returnActionResult(
            [
                'Points'=>array_reverse($returnData),
                'Connection'=>$this->parentPointsConnection
            ]
        );
    }

    public function TreeMode(){
        $pid=$this->get['PID'] ?? "";
        $mode=$this->get['mode'] ?? "content";
        $returnData=[
            'Tree'=>[]
        ];
        if (empty($pid)){
            return self::returnActionResult($returnData);
        }
        $subPoints=[];
        $this->getAllSubPointID($pid,$subPoints);
        $json=$this->createTree($subPoints,$mode);
        return self::returnActionResult(
            [
                'Data'=>$json
            ]
        );
    }

    public function getAllSubPointID($pid,&$returnData,$deep=0){
        static $endlessPrevent;
        if (isset($endlessPrevent[$pid])){
            return true;
        }
        $endlessPrevent[$pid]=1;
        // subPids
        $subPids=$this->pointsConnection->getSubParentId($pid);
        if (empty($subPids)){
            $this->SubPointsAmount++;
            return false;
        }
        $deep>0 && $this->maxSubDeep[$deep]=1;
        $deep++;
        foreach ($subPids as $subPid){
            !isset($returnData[$subPid]) && $returnData[$subPid]=[];
            $this->getAllSubPointID($subPid,$returnData[$subPid],$deep);
        }
        return true;
    }

    public function getAllParentPointID($subId,&$returnData,$deep=0){
        static $endlessPrevent;
        if (isset($endlessPrevent[$subId])){
            return true;
        }
        $endlessPrevent[$subId]=1;
        // parent id
        $parentIds=$this->pointsConnection->getParentId($subId);
        if (empty($parentIds)){
            $this->ParentPointsAmount++;
            return false;
        }
        !isset($this->parentPoints[$deep]) && $this->parentPoints[$deep]=[];
        $this->parentPoints[$deep][$subId]=$subId;
        $deep++;
        !isset($this->parentPoints[$deep]) && $this->parentPoints[$deep]=[];
        foreach ($parentIds as $parentId){
            $this->parentPointsConnection[]=[
                'Parent'=>$parentId,
                'SubParent'=>$subId
            ];
            if ($parentId==0){
                $this->ParentPointsAmount++;
                continue;
            }
            $this->parentPoints[$deep][$parentId]=$parentId;
            !isset($returnData[$parentId]) && $returnData[$parentId]=[];
            $this->getAllParentPointID($parentId,$returnData[$parentId],$deep);
        }
        return true;
    }

    public function createTree($data,$mode){
        $returnData=[];
        $index=0;
        foreach ($data as $pid=>$subPIDs){
            $point=$this->point->getPointDetail($pid);
            $title=$point['keyword'];
            switch ($mode){
                case "content":
                    if(!empty($point['note'])){
                        $title.=" /N";
                    }
                    if(!empty($point['file'])){
                        $title.=" /F";
                    }
                    break;
                case "status":
                    $title.=" (".$point['status'].")";
            }

            $returnData[$index]=[
                'title'=>$title,
                'value'=>$point['ID'],
                'key'=>$point['ID'],
                'children'=>$this->createTree($subPIDs,$mode)
            ];
            $index++;
        }
        return $returnData;
    }
}