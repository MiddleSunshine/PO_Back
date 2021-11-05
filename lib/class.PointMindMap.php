<?php
abstract class TablePart{
    abstract public static function getTableType();
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

    public static function getTableType()
    {
        return "Empty";
    }
}

class PointTable extends TablePart{
    public static function getTableType()
    {
        return 'Point';
    }

    protected static function getTableData($data = [])
    {
        return $data;
    }
}

class CD extends TablePart {
    public static function getTableType()
    {
        return "CD";
    }

    protected static function getTableData($data = [])
    {
        return [];
    }
}

class OneLine extends TablePart{
    public static function getTableType()
    {
        return "One_Line";
    }

    protected static function getTableData($data = [])
    {
        return [];
    }
}

class AC extends TablePart{
    public static function getTableType()
    {
        return "AC";
    }

    protected static function getTableData($data = [])
    {
        return [];
    }
}

class A extends TablePart{
    public static function getTableType()
    {
        return "A";
    }

    protected static function getTableData($data = [])
    {
        return [];
    }

}

class PointMindMap extends Base {
    public $pointsConnection;
    public $point;

    public $maxParentDeep=[];
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
        $dataBaseData=$this->getAllDataFromDataBase($id);
        $table=$this->createEmptyTable();
        $centerX=count($this->maxParentDeep);
        // put left data
        $this->putDataBaseDataIntoTable(
            $dataBaseData[0],
            $table,
            $centerX,
            0
        );
        // put right data
        $this->putDataBaseDataIntoTable(
            $dataBaseData[1],
            $table,
            $centerX+4,
            0,
            true
        );
        // put center data
        $table[0][$centerX]=PointTable::getTable($this->point->getPointDetail($id));
        // add the line
        $this->addTheLine($table);
        return self::returnActionResult(
            [
                'Table'=>array_values($table)
            ]
        );
    }

    public function addTheLine(&$table){
        foreach ($table as $outsideIndex=>$lines){
            foreach ($lines as $insideIndex=>$item){
                if ($item['Type']==PointTable::getTableType()){
                    if (isset($table[$outsideIndex][$insideIndex+2]) && $table[$outsideIndex][$insideIndex+2]['Type']==PointTable::getTableType()){
                        $table[$outsideIndex][$insideIndex+1]=CD::getTable();
                        if (isset($table[$outsideIndex+1][$insideIndex+1])){
                            $table[$outsideIndex+1][$insideIndex+1]==OneLine::getTable();
                        }
                    }
                    if (isset($table[$outsideIndex+2][$insideIndex]) && $table[$outsideIndex+2][$insideIndex]['Type']==PointTable::getTableType()){
                        if (isset($table[$outsideIndex+2][$insideIndex+2]) && $table[$outsideIndex+2][$insideIndex+2]['Type'==PointTable::getTableType()]){
                            $table[$outsideIndex][$insideIndex+1]=A::getTable();
                        }else{
                            $table[$outsideIndex][$insideIndex+1]=AC::getTable();
                            $table[$outsideIndex+1][$insideIndex+1]=OneLine::getTable();
                        }
                    }
                }
            }
        }
    }

    public function putDataBaseDataIntoTable($data,&$table,$x,$y,$addX=false){
        foreach ($data as $pid=>$subIds){
            $table[$y][$x]=PointTable::getTable($this->point->getPointDetail($pid));
            if (!empty($subIds)){
                $y=$this->putDataBaseDataIntoTable($subIds,$table,$addX?($x+2):($x-2),$y,$addX);
            }
            $y+=2;
        }
        return $y;
    }

    public function createEmptyTable(){
        $table=[];
        $outsideLength=max($this->SubPointsAmount,$this->ParentPointsAmount)*2;
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
        // parent id
        $parentIds=$this->pointsConnection->getParentId($subId);
        if (empty($parentIds)){
            $this->ParentPointsAmount++;
            return false;
        }
        $deep>0 && $this->maxParentDeep[$deep]=1;
        $deep++;
        foreach ($parentIds as $parentId){
            if ($parentId==0){
                $this->ParentPointsAmount++;
                continue;
            }
            !isset($returnData[$parentId]) && $returnData[$parentId]=[];
            $this->getAllParentPointID($parentId,$returnData[$parentId],$deep);
        }
        return true;
    }
}