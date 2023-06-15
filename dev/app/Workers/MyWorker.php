<?php

namespace App\Workers;

use Monken\CIBurner\Workerman\Worker\WorkerRegistrar;
use Workerman\Worker;

class MyWorker extends WorkerRegistrar
{
    public function initWorker(): Worker
    {
        $worker = new Worker();
        $worker->name = 'MyWorker';
        $worker->count = 1;
        $worker->onWorkerStart = function () {
            print_r('MyWorker is running' . PHP_EOL );
        };
        return $worker;
    }
}