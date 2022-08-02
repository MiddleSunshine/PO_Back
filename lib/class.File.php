<?php

class File extends Base{
    public static $fileType='md';

    public static function getFilePath($pid){
        return MD_FILE_INDEX.$pid.DIRECTORY_SEPARATOR;
    }
    public static function storeFile($pid,$fileName,&$storeContent){
        $filePath=self::getFilePath($pid);
        if (!is_dir($filePath)){
            mkdir($filePath);
        }
        if (empty($fileName)){
            return false;
        }
        $fileName=$filePath.$fileName.".".static::$fileType;
        file_put_contents($fileName,$storeContent);
        return file_exists($fileName);
    }

    public static function getFileContent($pid,$fileName){
        $filePath=self::getFilePath($pid);
        if (!is_dir($filePath)){
            return '';
        }
        $fileName=$filePath.$fileName.".".static::$fileType;
        if (file_exists($fileName)){
            return file_get_contents($fileName);
        }
        return '';
    }

    public static function storeData($filePath,&$storeContent){
        if (!file_exists($filePath)){
            return false;
        }
        file_put_contents($filePath,$storeContent);
        return true;
    }

    public static function getHostFilePath($pid){
        return LocalFilePath.DIRECTORY_SEPARATOR.$pid.DIRECTORY_SEPARATOR;
    }
}