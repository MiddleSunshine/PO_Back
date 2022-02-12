<?php
require_once __DIR__.DIRECTORY_SEPARATOR."config.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

use OSS\OssClient;

class UploadFile{
    protected $oss;
    public function __construct()
    {
        $this->oss=new OssClient(ACCESS_KEY_ID,ACCESS_KEY_SECRET,END_POINT);
    }

    public function Upload($bucket,$filePath,$fileName){
        $uploadResult=$this->oss->uploadFile(
            $bucket,
            $fileName,
            $filePath
        );
        return $uploadResult['oss-request-url'] ?? "";
    }
}