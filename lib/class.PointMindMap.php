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
        return '0,0,0,0';
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

class Plus extends TablePart{
    public static function getTableType()
    {
        return "Plus";
    }

    protected static function getTableData($data = [])
    {
        return implode(",",$data);
    }
}

class PointMindMap extends Base {
    public $pointsConnection;
    public $point;

    public $maxParentDeep=[];
    public $maxSubDeep=[];
    public $SubPointsAmount=0;
    public $ParentPointsAmount=0;

    public $yData=[];

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
        $centerX=count($this->maxParentDeep)*2+2;
        // put left data
        $this->putDataBaseDataIntoTable(
            $dataBaseData[0],
            $table,
            $centerX-2,
            0
        );
        // put right data
        $this->putDataBaseDataIntoTable(
            $dataBaseData[1],
            $table,
            $centerX+2,
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
                    continue;
                }
                $A=0;
                $B=0;
                $C=0;
                $D=0;
                // check A
                if (isset($table[$outsideIndex-1][$insideIndex]) && $table[$outsideIndex-1][$insideIndex]['Type']==Plus::getTableType()){
                    $data=explode(",",$table[$outsideIndex-1][$insideIndex]['Data']);
                    if ($data[2]==1){
                        $A=1;
                    }
                }
                // check B
                if (isset($table[$outsideIndex][$insideIndex+1]) && $table[$outsideIndex][$insideIndex+1]['Type']==PointTable::getTableType()){
                    $B=1;
                }
                // check C
                if (isset($this->yData[$outsideIndex][$insideIndex+1])){
                    $C=1;
                }
                if (isset($table[$outsideIndex][$insideIndex-1]) && $table[$outsideIndex][$insideIndex-1]['Type']==PointTable::getTableType()){
                    $D=1;
                }
                if (!$A && !$B && !$C && $D){
                    $D=0;
                }
                if (!$A && !$D && !$C && $B){
                    $B=0;
                }
                $table[$outsideIndex][$insideIndex]=Plus::getTable([
                    $A,$B,$C,$D
                ]);
            }
        }
    }

    public function putDataBaseDataIntoTable($data,&$table,$x,$y,$addX=false){
        $startY=$y;
        $hasData=false;
        $endY=$y;
        foreach ($data as $pid=>$subIds){
            $hasData=true;
            $endY=$y;
            $table[$y][$x]=PointTable::getTable($this->point->getPointDetail($pid));
            if (!empty($subIds)){
                $y=$this->putDataBaseDataIntoTable($subIds,$table,$addX?($x+2):($x-2),$y,$addX);
            }
            $y+=1;
        }
        if ($hasData){
            for ($i=$startY;$i<$endY;$i++){
                !isset($this->yData[$i]) && $this->yData[$i]=[];
                $this->yData[$i][$x]=1;
            }
        }
        return $y-1;
    }

    public function createEmptyTable(){
        $table=[];
        $outsideLength=max($this->SubPointsAmount,$this->ParentPointsAmount)+5;
        $insideLength=(count($this->maxParentDeep)+count($this->maxSubDeep))*2+5;
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