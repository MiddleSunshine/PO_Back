<?php
header("Access-Control-Allow-Origin: *");
require_once __DIR__.DIRECTORY_SEPARATOR."UploadFile.php";
require_once __DIR__.DIRECTORY_SEPARATOR."SyncMysql.php";

$method=$_GET['method'] ?? "";

switch ($method){
    case "Upload":
        $bucket=$_GET['bucket'] ?? LONG_STORE_BUCKET;
        $uploadFile=new UploadFile();
        $fileInfo=current($_FILES);
        try {
            $url=$uploadFile->Upload($bucket,$fileInfo['tmp_name'],time()."_".$fileInfo['name']);
            echo CommonReturn(true,"Success",['Url'=>$url]);
        }catch (Exception $e){
            echo CommonReturn(false,"Exception",[$e->getMessage(),$e->getFile(),$e->getLine()]);
        }catch (Throwable $e){
            echo CommonReturn(false,"Throwable",[$e->getMessage(),$e->getFile(),$e->getLine()]);
        }
        break;
    case "Mysql":
        try {
            $SyncMysql=new SyncMysql();
            $SyncMysql->mysqldumper();
        }catch (Exception $e){
            echo CommonReturn(false,"Exception",[$e->getMessage(),$e->getFile(),$e->getLine()]);
        }catch (Throwable $e){
            echo CommonReturn(false,"Throwable",[$e->getMessage(),$e->getFile(),$e->getLine()]);
        }
        break;
    default:
        echo CommonReturn(false,"Method Not Exists");
}

function CommonReturn($isSuccess=true,$message='',$data=[]){
    return json_encode([
        'Status'=>$isSuccess?1:0,
        'Message'=>$message,
        'Data'=>$data
    ]);
}