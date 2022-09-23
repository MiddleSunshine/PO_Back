<?php
use Workerman\Lib\Timer;

class SyncJob
{
    /**
     * @param $worker Worker
     * @return void
     */
    public function onWorkerStart($worker)
    {
        Timer::add(5 * 60, [$this, 'sendDataToElasticSearch'], [$worker]);
    }

    public function sendDataToElasticSearch()
    {
        $cmd=sprintf("/opt/bitnami/php/bin/php %s/MeilisearchIndex.php --method=SyncData",INCLUDE_ROOT);
        exec($cmd);
    }
}