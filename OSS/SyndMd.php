<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "config.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "UploadFile.php";

class SyndMd
{
    public function syncMd()
    {
        $filePath=__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR;
        $preFilePath=$filePath."md_back_up".DIRECTORY_SEPARATOR;
        if (!is_dir($preFilePath)){
            mkdir($preFilePath);
        }
        $storeFilePath=$preFilePath.date("Y-m-d").".tar";
        $cmd=sprintf(" tar -cvf %s %s",$storeFilePath,$filePath."md");
        exec($cmd);
        if (file_exists($storeFilePath)){
            $oss=new UploadFile();
            $oss->Upload(LONG_STORE_BUCKET,$storeFilePath,"Md_BackUp".DIRECTORY_SEPARATOR.date("Y-m-d").".tar");
        }
    }

    public static function getFiles($dir)
    {
        if (!is_dir($dir)) {
            return [];
        }
        $files = scandir($dir);
        unset($files[0]);
        unset($files[1]);
        return $files;
    }
}