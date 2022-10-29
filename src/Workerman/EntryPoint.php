<?php

namespace Monken\CIBurner\Workerman;

class EntryPoint
{
    public static function run()
    {
        $nowDir     = __DIR__;
        $workerPath = $nowDir . DIRECTORY_SEPARATOR . 'Worker.php';
        $start      = popen("php {$workerPath} start", 'w');
        pclose($start);
        echo PHP_EOL;
    }
}
