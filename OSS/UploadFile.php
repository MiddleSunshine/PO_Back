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

    public function Upload($filePath,$fileName){
        $uploadResult=$this->oss->uploadFile(
            BUCKET,
            $filePath,
            $fileName
        );
        return $uploadResult['oss-request-url'] ?? "";
    }
}