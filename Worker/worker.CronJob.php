<?php
require_once dirname(__DIR__).DIRECTORY_SEPARATOR."OSS".DIRECTORY_SEPARATOR."SyncMysql.php";

class CronJob{
    public function mysqlDumper(){
        $syncMysql=new SyncMysql();
        $syncMysql->mysqldumper();
    }
}