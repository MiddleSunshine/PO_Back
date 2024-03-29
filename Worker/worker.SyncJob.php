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
        $cmd=sprintf("/usr/bin/php %s/MeilisearchIndex.php --method=SyncData",dirname(__DIR__));
        exec($cmd);
    }
}