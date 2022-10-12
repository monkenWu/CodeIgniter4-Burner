<?php

namespace Monken\CIBurner\OpenSwoole;

class EntryPoint
{
    public static function run()
    {
        $nowDir     = __DIR__;
        $workerPath = $nowDir . DIRECTORY_SEPARATOR . 'Worker.php';
        $start      = popen("php {$workerPath}", 'w');
        pclose($start);
    }
}
