<?php
abstract class TablePart{
    abstract protected static function getTableType();
    abstract protected static function getTableData($data=[]);
    public static function getTable($data=[]){
        return [
            'Type'=>static::getTableType(),
            'Data'=>static::getTableData($data)
        ];
    }
}

class EmptyTable extends TablePart{
    protected static function getTableData($data=[])
    {
        return [];
    }

    protected static function getTableType()
    {
        return "Empty";
    }
}

class PointMindMap extends Base {
    public $pointsConnection;
    public $point;

    public $maxParentDeep=[];
    public $maxSubDeep=[];
    public $maxSubPoints=0;

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
        $dataBaseData=$this->getAllDataFromDataBase($id);
        $table=$this->createEmptyTable();
        
    }

    public function createEmptyTable(){
        $table=[];
        $outsideLength=$this->maxSubPoints*2;
        $insideLength=(count($this->maxParentDeep)+count($this->maxSubDeep))*2+1;
        for ($outsideIndex=0;$outsideIndex<$outsideLength;$outsideIndex++){
            $table[$outsideIndex]=[];
            for ($insideIndex=0;$insideIndex<$insideLength;$insideIndex++){
                $table[$outsideIndex][$insideIndex]=EmptyTable::getTable();
            }
        }
        return $table;
    }

    public function getAllDataFromDataBase($id){
        $returnData=[
            0=>[],
            1=>[]
        ];
        $this->getAllParentPointID($id,$returnData[0]);
        $this->getAllSubPointID($id,$returnData[1]);
        return $returnData;
    }

    public function getAllSubPointID($pid,&$returnData,$deep=0){
        // subPids
        $subPids=$this->pointsConnection->getSubParentId($pid);
        if (empty($subPids)){
            return true;
        }
        $deep>0 && $this->maxSubDeep[$deep]=1;
        $subPidAmount=count($subPids);
        $subPidAmount>$this->maxSubPoints && $this->maxSubPoints=$subPidAmount;
        $deep++;
        foreach ($subPids as $subPid){
            !isset($returnData[$subPid]) && $returnData[$subPid]=[];
            $this->getAllSubPointID($subPid,$returnData[$subPid],$deep);
        }
    }

    public function getAllParentPointID($subId,&$returnData,$deep=0){
        // parent id
        $parentIds=$this->pointsConnection->getParentId($subId);
        if (empty($parentIds)){
            return true;
        }
        $deep>0 && $this->maxParentDeep[$deep]=1;
        $parentIdAmount=count($parentIds);
        $parentIdAmount>$this->maxSubPoints && $this->maxSubPoints=$parentIdAmount;
        $deep++;
        foreach ($parentIds as $parentId){
            if ($parentId==0){
                continue;
            }
            !isset($returnData[$parentId]) && $returnData[$parentId]=[];
            $this->getAllParentPointID($parentId,$returnData[$parentId],$deep);
        }
    }
}