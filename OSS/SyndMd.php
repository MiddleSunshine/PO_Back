<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "config.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "UploadFile.php";

class SyndMd
{
    public function syncMd()
    {
        // todo 这里调整为使用zip打包的效果
//        $files = self::getFiles(MD_FILE_INDEX);
//        $upload = new UploadFile();
//        foreach ($files as $file) {
//            $mdFiles = self::getFiles(MD_FILE_INDEX . $file);
//            if (empty($mdFiles)) {
//                continue;
//            }
//            foreach ($mdFiles as $mdFile) {
//                $upload->Upload(LONG_STORE_BUCKET, MD_FILE_INDEX . $file . DIRECTORY_SEPARATOR . $mdFile, "PO_Back_MD_BackUp".DIRECTORY_SEPARATOR.date("Y-m-d") . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . $mdFile);
//            }
//        }
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