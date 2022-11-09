<?php

class Feeling extends Base{
    public static $table='Felling';
    protected $doNotCheckLogin=true;
    const TYPE_HAPPY='Happy';
    const TYPE_UNHAPPY='Unhappy';
    const TYPE_STAR='Star';
    const TYPE_IMPORTANT='Important';

    public function Save(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['Type'])){
            return self::returnActionResult($this->post,false,'Please select the mode');
        }
        $this->handleSql($this->post,0);
        return self::returnActionResult($this->post);
    }

    public function preSave()
    {
        $this->post['LastUpdateTime']=date("Y-m-d H:i:s");
    }

    public function List(){
        $startTime=empty($this->get['StartTime'])?date("Y-m-d 00:00:00",strtotime("-7 days")):$this->get['StartTime'];
        $endTime=empty($this->get['EndTime'])?date("Y-m-d H:i:s"):$this->get['EndTime'];
        $sql=sprintf("select * from %s where AddTime between '%s' and '%s' order by ID desc;",self::$table,$startTime,$endTime);
        $feelings=$this->pdo->getRows($sql);
        $returnData=[];
        $index=-1;
        $date=[];
        foreach ($feelings as $felling){
            $key=date("Y-m-d",strtotime($felling['AddTime']));
            if (!isset($date[$key])){
                $index++;
                $returnData[$index]=[
                    'label'=>$key,
                    'feelings'=>[]
                ];
            }
            $returnData[$index]['feelings'][]=$felling;
        }
        return self::returnActionResult($returnData);
    }
}
