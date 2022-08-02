<?php
require_once __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."config.php";
require_once __DIR__.DIRECTORY_SEPARATOR."UploadFile.php";

class SyncMysql{
    public function mysqldumper(){
        $templateDir=$this->templateDir();
        if (!$templateDir){
            return false;
        }
        $cmd=sprintf("mydumper -u %s -p %s -h %s -B %s -o %s",PROD_DB_USER,PROD_DB_PASS,PROD_DB_HOST,PROD_DB_NAME,$templateDir);
        exec($cmd);
        $files=scandir($templateDir);
        unset($files[0]);
        unset($files[1]);
        $oss=new UploadFile();
        foreach ($files as $file){
            $oss->Upload(LONG_STORE_BUCKET,$templateDir.$file,sprintf("%s/%s/%s","mysqldumper",date("Y-m-d-h"),$file));
        }
        $this->cleanTemplate($templateDir);
    }

    public function templateDir(){
        $dir=sprintf("/tmp/mysql_%s/",date("Y_m_d_H"));
        if (is_dir($dir)){
            return false;
        }
        mkdir($dir);
        return $dir;
    }

    public function cleanTemplate($dir){
        $cmd=sprintf("rm -rf %s",$dir);
        exec($cmd);
    }
}