<?php
require_once __DIR__.DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."config.php";

$process=basename(__FILE__);

if (!check_process($process)){
    print sprintf("%s process is still running%s",date("Y-m-d H:i:s"),PHP_EOL);
}

$search=new Search();

if (isset($_SERVER['argc']) && $_SERVER['argc'] > 1){
    foreach ($_SERVER['argv'] as $v){
        list($method,$value)=explode("=",$v);
        switch ($value){
            case "SyncData":
                $search->syncData();
                break;
            case "RefreshData":
                $search->refreshESAllData();
                echo $search->getError().PHP_EOL;
                break;
        }
    }
}