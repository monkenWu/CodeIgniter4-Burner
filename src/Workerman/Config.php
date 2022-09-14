<?php

namespace Monken\CIBurner\Workerman;

use Workerman\Connection\TcpConnection;
use Workerman\Worker;

class Config
{
    public static function instanceSetting(Worker &$worker, \Config\Workerman $config)
    {
        $worker->count      = $config->workerCount;
        $worker->user       = $config->workerUser;
        $worker->reloadable = $config->autoReload;
        $config->initWorker($worker);
    }

    public static function staticSetting(\Config\Workerman $config)
    {
        Worker::$stdoutFile                      = $config->stdoutFile;
        Worker::$logFile                         = $config->logFile;
        TcpConnection::$defaultMaxPackageSize    = $config->defaultMaxPackageSize;
        TcpConnection::$defaultMaxSendBufferSize = $config->defaultMaxSendBufferSize;
        StaticFile::forbid($config->staticForbid);
        StaticFile::publicPath($config->staticDir);
    }
}
