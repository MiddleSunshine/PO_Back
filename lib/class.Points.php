<?php

class Points extends Base{
    public static $autoSendPointKeyWord="连续登陆：自动赠送积分";

    public static $table="Points";

    const STATUS_INIT='init';
    const STATUS_NEW='new';
    const STATUS_SOLVED='solved';
    const STATUS_GIVE_UP='give_up';
    const STATUS_ARCHIVED='archived';

    const SEARCHABLE_POINT='Point';
    const SEARCHABLE_TITLE='Title';

    public static $statusMap=[
        self::STATUS_NEW=>0,
        self::STATUS_SOLVED=>1,
        self::STATUS_GIVE_UP=>2,
        self::STATUS_ARCHIVED=>3
    ];

    public $mindMapConnection=[];

    public function Index(){
        $pid=$this->get["id"] ?? 0;
        $this->post=json_decode($this->post,1);
        $status=empty($this->post["status"]) ? sprintf("%s,%s,%s",self::STATUS_NEW,self::STATUS_SOLVED,self::STATUS_INIT):$this->post["status"];
        $searchStatus=[];
        foreach (explode(",",$status) as $str){
            $searchStatus[]=sprintf("'%s'",$str);
        }
        $searchStatus=implode(",",$searchStatus);
        $pointsConnection=new PointsConnection();
        $subPids=$pointsConnection->getSubParentId($pid);
        if (empty($subPids)){
            return self::returnActionResult([]);
        }
        $returnData=[
            'points'=>[]
        ];
        foreach ($subPids as $subPid){
            $childrenPids=$pointsConnection->getSubParentId($subPid);
            $point=$this->getPointDetail($subPid,$searchStatus);
            if ($point){
                $point['children']=[];
                if (!empty($childrenPids)){
                    foreach ($childrenPids as $childrenPid){
                        $childrenPoint=$this->getPointDetail($childrenPid,$searchStatus);
                        if($childrenPoint){
                            $point['children'][]=$childrenPoint;
                        }
                    }
                }
                $returnData['points'][]=$point;
            }
        }
        return self::returnActionResult($returnData);
    }

    public function MindMapPoint(){
        $PID=$this->get['PID'] ?? 0;
        $subLevel=$this->get['SubLevel'] ?? 1;
        $parentLevel=$this->get['ParentLevel'] ?? 1;
        $returnData=[];
        $connectionInstance=new PointsConnection();
        $point=$this->getPointDetail($PID);
        $comment=new PointsComments();
        $point['Comments']=$comment->getComments($PID);
        $this->getParentPoint($connectionInstance->getParentId($PID),$parentLevel,$returnData,$parentLevel,$PID);
        $returnData[$parentLevel+1]=[
            $point
        ];
        $this->getSubPoint($connectionInstance->getSubParentId($PID),$subLevel,$returnData,$parentLevel+2,$PID);
        $filterData=[];
        for($i=0;$i<count($returnData);$i++){
            if(!empty($returnData[$i])){
                $filterData[]=$returnData[$i];
            }
        }
       return self::returnActionResult(
           [
               'Points'=>$filterData,
               'Connection'=>$this->mindMapConnection
           ]
       );
    }

    public function ReviewPoint(){
        $startTime=$this->get['StartTime'] ?? '';
        $endTime=$this->get['EndTime'] ?? date("Y-m-d");
        empty($endTime) && $endTime=date("Y-m-d");
        $indexPID=$this->get['PID'] ?? '';
        if (empty($startTime) || empty($indexPID)){
            return self::returnActionResult(
                $this->get,
                false,
                "Wrong Param"
            );
        }
        $pointMindMap=new PointMindMap();
        $connectionPoint=[];
        $pointMindMap->getAllParentPointID($indexPID,$connectionPoint);
        $pointMindMap->getAllSubPointID($indexPID,$connectionPoint);
        $sql=sprintf("select * from %s where LastUpdateTime between '%s 00:00:00' and '%s 23:59:59' order by LastUpdateTime;",self::$table,$startTime,$endTime);
        $points=$this->pdo->getRows($sql,'ID');
        foreach ($points as $Id=>&$point){
            if (!isset($connectionPoint[$Id])){
                unset($points[$Id]);
            }
            $point['FileContent']="";
            !empty($point['file']) && $point['FileContent']=File::getFileContent($Id,$point['file']);
        }
        return self::returnActionResult(
            [
                'Points'=>array_values($points)
            ]
        );
    }

    public function Search(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['keyword'])){
            return self::returnActionResult([]);
        }
        $sql="select * from ".static::$table." where keyword like '%".$this->post['keyword']."%' and Deleted=0 order by ID desc;";
        return self::returnActionResult(
            $this->pdo->getRows($sql),
            true,
            $sql
        );
    }

    public function GetFavouritePoints(){
        $sql=sprintf("select * from %s where Favourite='%s' and Deleted=0 order by ID desc;",static::$table,'Favourite');
        return self::returnActionResult(
            $this->pdo->getRows($sql)
        );
    }

//    public function SearchPointList(){
//        $sql=sprintf(
//            "select ID,keyword from %s where Deleted=0 and SearchAble='%s';",
//            static::$table,
//            self::SEARCHABLE_YES
//        );
//        return self::returnActionResult([
//            'Points'=>$this->pdo->getRows($sql)
//        ]);
//    }

    public function UpdatePoint(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['keyword'])){
            return self::returnActionResult($this->post,false,"keyword不能为空");
        }
        if (empty($this->post['ID'])){
            return self::returnActionResult($this->post,false,"数据错误");
        }
        $sql=sprintf("select * from %s where keyword='%s' and ID!=%d;",static::$table,$this->post['keyword'],$this->post['ID']);
        $point=$this->pdo->getFirstRow($sql);
        if ($point['ID']){
            return self::returnActionResult(['point'=>$point],false,"Point已经存在");
        }
        $this->handleSql($this->post,$this->post['ID']);
        $search=new Search();
        $search->addQueue($point['ID']);
        return self::returnActionResult($this->post);
    }

    public function Save($checkPid=true){
        $this->post=json_decode($this->post,1);
        $point=$this->post['point'];
        $forceUpdate=$this->post['forceUpdate'] ?? true;
        if (empty($point['keyword'])){
            return self::returnActionResult([],false,"keyword不能为空");
        }
        if ($checkPid && !isset($this->post['PID'])){
            return self::returnActionResult([],false,"PID Error");
        }
        if (!empty($point['ID'])){
            // update
            $point['LastUpdateTime']=date("Y-m-d H:i:s");
            array_map("trim",$point);
            $this->handleSql($point,$point['ID'],'keyword');
        }else{
            unset($point['ID']);
            $sql=sprintf("select ID from %s where keyword='%s';",self::$table,$point['keyword']);
            $lastPoint=$this->pdo->getFirstRow($sql);
            if ($lastPoint && !$forceUpdate){
                return self::returnActionResult([
                    'LastID'=>$lastPoint['ID']
                ],false,"该值已存在，ID：".$lastPoint['ID']);
            }
            // insert
            $point['AddTime']=date("Y-m-d H:i:s");
            $point['LastUpdateTime']=date("Y-m-d H:i:s");
            empty($point['status']) && $point['status']=self::STATUS_INIT;
            array_map("trim",$point);
            $this->post['keyword']=$point['keyword'];
            $this->handleSql($point,0,'keyword');
        }
        $sql=sprintf("select ID,status from %s where keyword='%s'",static::$table,$point['keyword']);
        $point=$this->pdo->getFirstRow($sql);
        $pid=$this->post['PID'];
        if ($checkPid){
            $pointsConnection=new PointsConnection();
            $pointsConnection->updatePointsConnection($pid,$point['ID']);
        }
        $search=new Search();
        $search->addQueue($point['ID']);
        return self::returnActionResult([
            'ID'=>$point['ID'],
            'Status'=>$point['status']
        ]);
    }

    public function CommonDelete()
    {
        parent::CommonDelete();
        if (!empty($this->get['ID'])){
            $search=new Search();
            $search->addQueue($this->get['ID'],Search::OPTION_DELETE);
            $sql=sprintf("delete from %s where PID=%d;",PointsConnection::$table,$this->get["ID"]);
            $this->pdo->query($sql);
            $sql=sprintf("delete from %s where SubPID=%d;",PointsConnection::$table,$this->get['ID']);
            $this->pdo->query($sql);
        }
        return self::returnActionResult();
    }

    public function GetAPoint(){
        $id=$this->get['id'] ?? 0;
        if (!$id){
            return self::returnActionResult([],false,"id error");
        }
        $sql=sprintf("select * from %s where ID=%d",static::$table,$id);
        return self::returnActionResult(
            $this->pdo->getFirstRow($sql)
        );
    }

    public function GetDetailWithFile(){
        $id=$this->get['ID'] ?? 0;
        if (!$id){
            return self::returnActionResult([]);
        }
        $sql=sprintf("select * from %s where ID=%d",static::$table,$id);
        $point=$this->pdo->getFirstRow($sql);
        if (!$point){
            return self::returnActionResult([]);
        }
        return self::returnActionResult([
            'Point'=>$point,
            'FileContent'=>File::getFileContent($id,$point['file']),
            'LocalFilePath'=>File::getHostFilePath($id)
        ]);
    }

    public function SaveWithFile(){
        $dataBaseSaveResult=$this->Save(false);
        if (!$dataBaseSaveResult['Status']){
            return $dataBaseSaveResult;
        }
        !is_array($this->post) && $this->post=json_decode($this->post,1);
        $fileContent=$this->post['FileContent'];
        $fileContent && File::storeFile($dataBaseSaveResult['Data']['ID'],$this->post['point']['file'],$fileContent);
        return $dataBaseSaveResult;
    }

    public function WithoutConnectionPoints(){
        $sql1=sprintf("select * from %s where ID not in (
            select PID from %s
            union
            select SubPID from %s
        ) and Deleted=0 and `status`!='%s';
        ",self::$table,PointsConnection::$table,PointsConnection::$table,self::STATUS_ARCHIVED);
        $sql2=sprintf("select * from %s where Deleted=1",self::$table);
        return self::returnActionResult(
            [
                'Points'=>array_merge(
                    $this->pdo->getRows($sql1),
                    $this->pdo->getRows($sql2)
                )
            ]
        );
    }

    public function getPointDetail($pid,$staus=''){
        if ($staus){
            $sql=sprintf("select ID,keyword,status,Point,Favourite,note,file,SearchAble from %s where ID=%d and status in (%s) and Deleted=0",static::$table,$pid,$staus);
        }else{
            $sql=sprintf("select ID,keyword,status,Point,Favourite,note,file,SearchAble from %s where ID=%d and Deleted=0;",static::$table,$pid);
        }
        return $this->pdo->getFirstRow($sql);
    }

    public function getParentPoint($pointIds,$level,&$returnData,$index,$prePointId){
        static $pointConnectionInstance,$pointCommentInstance;
        !$pointConnectionInstance && $pointConnectionInstance=new PointsConnection();
        !$pointCommentInstance && $pointCommentInstance=new PointsComments();
        if ($level<0){
            return false;
        }
        if (empty($pointIds)){
            return false;
        }
        if ($index<0){
            return false;
        }
        !isset($returnData[$index]) && $returnData[$index]=[];
        $sql=sprintf("select * from %s where ID in (%s);",static::$table,implode(",",$pointIds));
        $points=$this->pdo->getRows($sql);
        if(empty($sql)){
            return false;
        }
        foreach ($points as $point){
            !isset($this->mindMapConnection[$point['ID']]) && $this->mindMapConnection[$point['ID']]=[];
            $this->mindMapConnection[$point['ID']][]=$prePointId;
            $point['Comments']=$pointCommentInstance->getComments($point['ID']);
            $returnData[$index][]=$point;
            $this->getParentPoint(
                $pointConnectionInstance->getParentId($point['ID']),
                $level-1,
                $returnData,
                $index-1,
                $point['ID']
            );
        }
    }

    public function getSubPoint($pointIds,$level,&$returnData,$index,$prePointId){
        static $pointConnectionInstance,$pointCommentInstance;
        !$pointConnectionInstance && $pointConnectionInstance=new PointsConnection();
        !$pointCommentInstance && $pointCommentInstance=new PointsComments();
        if ($level<0){
            return false;
        }
        if (empty($pointIds)){
            return false;
        }
        !isset($returnData[$index]) && $returnData[$index]=[];
        $sql=sprintf("select * from %s where ID in (%s);",static::$table,implode(",",$pointIds));
        $points=$this->pdo->getRows($sql);
        $this->mindMapConnection[$prePointId]=[];
        foreach ($points as $point){
            $this->mindMapConnection[$prePointId][]=$point['ID'];
            $childPoints=$pointConnectionInstance->getSubParentId($point['ID']);
            $point['Comments']=$pointCommentInstance->getComments($point['ID']);
            $returnData[$index][]=$point;
            $this->getSubPoint($childPoints,$level-1,$returnData,$index+1,$point['ID']);
        }
    }

    public function AutoSend(){
        // 判断今天是否已经领取过积分了
        $todayKey=self::getKeywordName(date("Y-m-d"));
        $sql=sprintf(
            "select * from %s where Keyword='%s'",
            Points::$table,
            $todayKey
        );
        $today=$this->pdo->getFirstRow($sql);
        if ($today){
            return self::returnActionResult([],false,"今天已经领取过积分了");
        }
        $this->post=json_encode([
            'point'=>[
                'keyword'=>$todayKey,
                'status'=>self::STATUS_ARCHIVED,
                'Point'=>10
            ]
        ]);
        $this->Save(false);
        return self::returnActionResult([
            'Point'=>10
        ]);
    }

    public static function getKeywordName($date){
        return static::$autoSendPointKeyWord." @ ".$date;
    }
}