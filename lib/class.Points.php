<?php

class Points extends Base{
    public static $autoSendPointKeyWord="连续登陆：自动赠送积分";

    public static $table="Points";

    const STATUS_NEW='new';
    const STATUS_SOLVED='solved';
    const STATUS_GIVE_UP='give_up';
    const STATUS_ARCHIVED='archived';

    public static $statusMap=[
        self::STATUS_NEW=>0,
        self::STATUS_SOLVED=>1,
        self::STATUS_GIVE_UP=>2,
        self::STATUS_ARCHIVED=>3
    ];

    public function Index(){
        $pid=$this->get["id"] ?? 0;
        $this->post=json_decode($this->post,1);
        $status=$this->post["status"] ?? sprintf("%s,%s",self::STATUS_NEW,self::STATUS_SOLVED);
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

    public function Save($checkPid=true){
        $postData=json_decode($this->post,1);
        $point=$postData['point'];
        $forceUpdate=$postData['forceUpdate'] ?? true;
        if (empty($point['keyword'])){
            return self::returnActionResult([],false,"keyword不能为空");
        }
        if ($checkPid && !isset($postData['PID'])){
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
            empty($point['status']) && $point['status']=self::STATUS_NEW;
            array_map("trim",$point);
            $this->post['keyword']=$point['keyword'];
            $this->handleSql($point,0,'keyword');
        }
        $sql=sprintf("select ID,status from %s where keyword='%s'",static::$table,$point['keyword']);
        $point=$this->pdo->getFirstRow($sql);
        $pid=$postData['PID'];
        if ($checkPid){
            $pointsConnection=new PointsConnection();
            $pointsConnection->updatePointsConnection($pid,$point['ID']);
        }
        return self::returnActionResult([
            'ID'=>$point['ID'],
            'Status'=>$point['status']
        ]);
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
            'LocalFilePath'=>File::getHostFilePath($id,$point['file'])
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

    public function getPointDetail($pid,$staus=''){
        if ($staus){
            $sql=sprintf("select ID,keyword,status,Point,Favourite from %s where ID=%d and status in (%s) and Deleted=0",static::$table,$pid,$staus);
        }else{
            $sql=sprintf("select ID,keyword,status,Point,Favourite from %s where ID=%d and Deleted=0;",static::$table,$pid);
        }
        return $this->pdo->getFirstRow($sql);
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