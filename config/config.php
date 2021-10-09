<?php

// 允许跨域
header("Access-Control-Allow-Origin: *");

define("PROD_DB_NAME","PO");
define("PROD_DB_HOST","172.19.0.8");
define("PROD_DB_USER","root");
define("PROD_DB_PASS","1234qwer");
define("PROD_DB_SOCKET","");
define("DEBUG_MODE",true);
define("MYSQL_SET_NAMES","utf8");
define("INDEX_FILE",dirname(__DIR__));
define("MD_FILE_INDEX",INDEX_FILE.DIRECTORY_SEPARATOR."md".DIRECTORY_SEPARATOR);
define("LocalFilePath","/Users/yangqingxian/Documents/PO/PO/back/php/PO_Back/md");

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

function debug($logFileName,$content){
    file_put_contents(INDEX_FILE.DIRECTORY_SEPARATOR."log".DIRECTORY_SEPARATOR.$logFileName.".log",date("Y-m-d H:i:s").PHP_EOL.$content.PHP_EOL,FILE_APPEND);
}