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
        if (empty($this->post['type'])){
            return self::returnActionResult($this->post,false,'Please select the mode');
        }
        $this->post['imageUrls']=empty($this->post['imageUrls'])?'[]':json_encode($this->post['imageUrls']);
        $this->handleSql($this->post,0);
        return self::returnActionResult($this->post);
    }

    public function preSave()
    {
        $this->post['imageUrls']=empty($this->post['imageUrls'])?'[]':json_encode($this->post['imageUrls']);
        $this->post['LastUpdateTime']=date("Y-m-d H:i:s");
    }

    public function List(){
        $startTime=empty($this->get['StartTime'])?date("Y-m-d 00:00:00",strtotime("-7 days")):$this->get['StartTime'];
        $endTime=empty($this->get['EndTime'])?date("Y-m-d H:i:s"):$this->get['EndTime'];
        $sql=sprintf("select * from %s where AddTime between '%s' and '%s' order by ID desc;",self::$table,$startTime,$endTime);
        $feelings=$this->pdo->getRows($sql);
        $storeData=$returnData=[];
        foreach ($feelings as $felling){
            $felling['imageUrls']=empty($felling['imageUrls'])?[]:json_decode($felling['imageUrls'],1);
            $felling['AddTimeStamp']=strtotime($felling['AddTime']);
            $key=date("Y-m-d",$felling['AddTimeStamp']);
            !isset($storeData[$key]) && $storeData[$key]=[
                'date'=>$key." (周".static::getLocalDateN($felling['AddTimeStamp']).")",
                'feelings'=>[]
            ];
            $storeData[$key]['feelings'][$felling['ID']]=$felling;
        }
        foreach ($storeData as $item){
            $item['feelings']=array_values(array_reverse($item['feelings']));
            $returnData[]=$item;
        }
        
        return self::returnActionResult($returnData);
    }
}
