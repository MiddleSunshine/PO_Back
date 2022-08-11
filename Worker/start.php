<?php

use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . "worker.SyncJob.php";

define("INCLUDE_ROOT",dirname(__DIR__));

$syncJob = new SyncJob();
$syncJobWorker = new Worker();
$syncJobWorker->name = "Sync Jobs";
$syncJobWorker->onWorkerStart = [$syncJob, 'onWorkerStart'];


// 运行worker
Worker::runAll();