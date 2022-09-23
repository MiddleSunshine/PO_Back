<?php

use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . "worker.SyncJob.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "worker.CronJob.php";

define("INCLUDE_ROOT", dirname(__DIR__));
date_default_timezone_set("Asia/Shanghai");

printf("%sStart @%s%s", PHP_EOL, date("Y-m-d H:i:s"), PHP_EOL);

$syncJob = new SyncJob();
$syncJobWorker = new Worker();
$syncJobWorker->name = "Sync Jobs";
$syncJobWorker->onWorkerStart = [$syncJob, 'onWorkerStart'];

$cronJobWorker = new Worker();
$cronJobWorker->name = "Cron Jobs";
$cronJobWorker->onWorkerStart = function () {
    new Workerman\Crontab\Crontab(
        "0 1 13 * * *", function () {
        $cronJob = new CronJob();
        $cronJob->mysqlDumper();
    }
    );
    new Workerman\Crontab\Crontab(
        "0 1 23 * * 0", function () {
            $backUp=new SyndMd();
            $backUp->syncMd();
    }
    );
};

// 运行worker
Worker::runAll();