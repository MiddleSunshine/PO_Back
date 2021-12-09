<?php
require_once __DIR__.DIRECTORY_SEPARATOR."class.MysqlPdo.php";

class Base{
    public static $table='';
    protected $get;
    protected $post;
    public $pdo;
    public function __construct($get=[],$post='')
    {
        $this->get=$get;
        $this->post=$post;
        $this->pdo=new MysqlPdo();
    }

    public function List(){
        $page=$this->get['page'] ?? 0;
        $pageSize=$this->get['page_size'] ?? 0;
        $sql="select * from ".static::$table;
        if ($page && $pageSize){
            $sql=sprintf($sql." limit %d,%d",($page-1)*$pageSize,$pageSize);
        }
        $sql.=" order by ID desc;";
        $data=$this->pdo->getRows($sql);
        self::addKey($data,'ID','key');
        return self::returnActionResult($data);
    }

    public function preSave(){

    }

    public function CommonSave(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['ID'])){
            return self::returnActionResult($this->post,false,"Error Data");
        }
        $this->preSave();
        return $this->handleSql($this->post,$this->post['ID']);
    }

    public function CommonDelete(){
        $id=$this->get['ID'] ?? 0;
        if ($id<=0){
            return self::returnActionResult($this->get,false,"Wrong Param !");
        }
        $sql=sprintf("delete from %s where ID=%d",static::$table,$id);
        $this->pdo->query($sql);
        return self::returnActionResult();
    }

    public function Detail(){
        $id=$this->get['id'] ?? 0;
        if (!$id){
            return self::returnActionResult([],false,"参数错误，没有ID");
        }
        $sql=sprintf("select * from %s where ID={$id};",static::$table);
        return self::returnActionResult($this->pdo->getFirstRow($sql));
    }

    public static function returnActionResult($returnData=[],$isSuccess=true,$message=''){
        return [
            'Status'=>$isSuccess?1:0,
            'Message'=>$message,
            'Data'=>$returnData
        ];
    }
    public static function addKey(&$data,$keyName,$newKeyName){
        foreach ($data as &$value){
            $value[$newKeyName]=$value[$keyName];
        }
    }
    public function handleSql($sql,$id,$keyName='',$getNewestData=false){
        $tableField=$this->getTableField();
        if($id){
            $sqlTemplate=[];
            foreach ($sql as $filed=>$value){
                if (!in_array($filed,$tableField)){
                    continue;
                }
                if ($value!==null){
                    $sqlTemplate[]=sprintf("%s='%s'",$filed,addslashes($value));
                }else{
                    $sqlTemplate[]=sprintf("%s=null",$filed);
                }
            }
            $sql=implode(",",$sqlTemplate);
            // udpate
            $sql=sprintf("update %s set {$sql} where ID={$id};",static::$table);
        }else{
            $sqlTemplate='';
            $fields=[];
            foreach ($sql as $filed=>$value){
                if (!in_array($filed,$tableField)){
                    continue;
                }
                $fields[]=$filed;
                if ($value===null){
                    $sqlTemplate.="null,";
                }else{
                    $sqlTemplate.=sprintf("'%s',",addslashes($value));
                }
            }
            $sqlTemplate=substr($sqlTemplate,0,-1);
            // insert 之前已有的值，然后就会变成 update
            if (!empty($keyName)){
                $sqlSearch=sprintf("select {$keyName},ID from %s where {$keyName}='%s'",static::$table,addslashes($this->post[$keyName]));
                $data=$this->pdo->getFirstRow($sqlSearch);
                if (!empty($data)){
                    return $this->handleSql($sql,$data['ID'],$keyName);
                }
            }
            // insert
            $sql=sprintf("insert into %s(%s) value(%s)",static::$table,implode(",",$fields),$sqlTemplate);
        }
        $this->pdo->query($sql);
        if ($id){
            return self::returnActionResult(
                [
                    'sql'=>$sql,
                    'ID'=>$id
                ]
            );
        }
        if ($getNewestData){
            $sql=sprintf("select ID from %s order by ID desc limit 1;",static::$table);
        }else{
            if (!empty($keyName)){
                $sql=sprintf("select ID from %s where {$keyName}='%s';",static::$table,addslashes($this->post[$keyName] ?? ''));
            }else{
                $sql=null;
            }
        }
        if (!empty($sql)){
            $word=$this->pdo->getFirstRow($sql);
        }
        return self::returnActionResult([
            'sql'=>$sql,
            'ID'=>$word['ID'] ?? 0
        ]);
    }

    public function getTableField($table=''){
        $sql="desc ".($table?:static::$table);
        $columns=$this->pdo->getRows($sql);
        return array_column($columns,'Field');
    }

    public function removeDeleted($row){
        return $row['Deleted']==0;
    }

    public static function getDateRange($startTime,$endTime,$dateFormat){
        $returnData[]=date($dateFormat,strtotime($startTime));
        $startTime=strtotime($startTime);
        $endTime=strtotime($endTime);
        $oneDay=24*60*60;
        while ($startTime<=$endTime){
            $returnData[]=date($dateFormat,$startTime+$oneDay);
            $startTime+=$oneDay;
        }
        return $returnData;
    }
}