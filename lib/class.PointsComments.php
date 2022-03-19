<?php

class PointsComments extends Base{
    public static $table='Points_Comments';

    public function NewComment(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['Comment']) && empty($this->post['Md'])){
            return self::returnActionResult([],false,"Empty Content");
        }
        $this->post['AddTime']=date("Y-m-d H:i:s");
        $this->post['LastUpdateTime']=date('Y-m-d H:i:s');
        $this->handleSql($this->post,0);
        return self::returnActionResult($this->post);
    }

    public function GetLastComment(){
        $limit=$this->get['limit'] ?? 20;
        $sql=sprintf("select * from %s order by ID desc limit %d",static::$table,$limit);
        $comments=$this->pdo->getRows($sql);
        $PIDs=array_unique(array_column($comments,'PID'));
        if (!empty($PIDs)){
            $sql=sprintf("select ID,keyword,status from %s where ID in (%s)",Points::$table,implode(",",$PIDs));
            $points=$this->pdo->getRows($sql,'ID');
        }
        foreach ($comments as &$comment){
            $comment['Point']=$points[$comment['PID']];
        }
        return self::returnActionResult([
            'Comments'=>$comments
        ]);
    }

    public function Comments(){
        $PID=$this->get['PID'] ?? 0;
        if (empty($PID)){
            return self::returnActionResult($this->get,false,"Error Param");
        }
        $sql=sprintf("select * from %s where PID=%d order by ID desc;",self::$table,$PID);
        return self::returnActionResult(
            [
                'Comments'=>$this->pdo->getRows($sql)
            ]
        );
    }

    public function preSave()
    {
        $this->post['LastUpdateTime']=date('Y-m-d H:i:s');
    }
}