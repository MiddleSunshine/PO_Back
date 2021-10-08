<?php

class Willing extends Base{
    public static $table="Willing";

    const STATUS_NEW='new';
    const STATUS_EXCHANGED='exchanged';

    public function List()
    {
        $returnData=parent::List();
        $returnData['Data']=[
            'Points'=>$returnData['Data']
        ];
        foreach ([self::STATUS_NEW,self::STATUS_EXCHANGED] as $status){
            $sql=sprintf("select sum(Point) as %s from %s where status='%s'",$status,static::$table,$status);
            $amount=$this->pdo->getFirstRow($sql);
            $returnData['Data']['amount'][$status]=$amount[$status];
        }
        $sql=sprintf("select sum(Point) as Point from %s where status in (%s)",Points::$table,"'".Points::STATUS_SOLVED."','".Points::STATUS_ARCHIVED."'");
        $amount=$this->pdo->getFirstRow($sql);
        $returnData['Data']['amount']['Point']=(($amount['Point'] ?? 0)-$returnData['Data']['amount'][self::STATUS_EXCHANGED]);
        return $returnData;
    }

    public function Save(){
        $this->post=json_decode($this->post,1);
        $this->post['LastUpdateTime']=date("Y-m-d H:i:s");
        if (empty($this->post['note'])){
            return self::returnActionResult([],false,"Title can't be empty!");
        }
        empty($this->post['Point']) && $this->post['Point']=0;
        if (!empty($this->post['ID'])){
            // update
            $id=$this->post['ID'];
            unset($this->post['ID']);
            $this->handleSql($this->post,$id,'ID');
        }else{
            // insert
            $this->post['AddTime']=date("Y-m-d H:i:s");
            $this->handleSql($this->post,'','ID');
            $sql=sprintf("select ID from %s order by ID desc limit 1",static::$table);
            $willing=$this->pdo->getFirstRow($sql);
            $id=$willing['ID'];
        }
        return self::returnActionResult([
            'ID'=>$id
        ]);
    }
}