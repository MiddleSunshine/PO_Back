<?php

// 允许跨域
header("Access-Control-Allow-Origin: *");
ini_set('date.timezone','Asia/Shanghai');
define("PROD_DB_NAME","PO");
define("PROD_DB_HOST","172.19.0.8");
define("PROD_DB_USER","root");
define("PROD_DB_PASS","1234qwer");
define("PROD_DB_SOCKET","");
define("DEBUG_MODE",true);
define("MYSQL_SET_NAMES","utf8");
define('TIME_ZONE','Asia/Shanghai');
define("INDEX_FILE",dirname(__DIR__));
define("MD_FILE_INDEX",INDEX_FILE.DIRECTORY_SEPARATOR."md".DIRECTORY_SEPARATOR);
define("BOOK_MARK_INDEX",INDEX_FILE.DIRECTORY_SEPARATOR."bookmarker".DIRECTORY_SEPARATOR);
define("POINT_COLLECT_INDEX",INDEX_FILE.DIRECTORY_SEPARATOR."point_collect".DIRECTORY_SEPARATOR);
define("LocalFilePath","/Users/yangqingxian/Documents/PO/PO/back/php/PO_Back/md");
define("SummaryFilePath",INDEX_FILE.DIRECTORY_SEPARATOR."summary");

define("ES_SERVER","http://172.19.0.9:9200");

function __autoload($class){
    $fileName=INDEX_FILE.DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."class.".$class.".php";
    if (file_exists($fileName)){
        require_once $fileName;
    }else{
        echo $fileName.PHP_EOL;
        echo "not exists".PHP_EOL;
        exit();
    }
}

// spl_autoload_register("__autoload2");

if(defined("BOOK_MARK_INDEX") && !is_dir(BOOK_MARK_INDEX)){
    mkdir(BOOK_MARK_INDEX);
}

if (defined("POINT_COLLECT_INDEX") && !is_dir(POINT_COLLECT_INDEX)){
    mkdir(POINT_COLLECT_INDEX);
}

function debug($logFileName,$content){
    file_put_contents(INDEX_FILE.DIRECTORY_SEPARATOR."log".DIRECTORY_SEPARATOR.$logFileName.".log",date("Y-m-d H:i:s").PHP_EOL.$content.PHP_EOL,FILE_APPEND);
}


function getFirstAndLastDay($date)
{
    $firstday = date('Y-m-01',strtotime($date));
    $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
    return [
        $firstday,
        $lastday
    ];
}

function check_process($process) {
    $cmd = `ps aux | grep $process | grep 'grep' -v | grep '/bin/sh' -v -c`;
    $count = '' . $cmd . '';
    if ($count > 1) {
        return false;
    } else {
        return true;
    }
}