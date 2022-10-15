<?php

namespace Monken\CIBurner\OpenSwoole;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\Factories;

class EntryPoint
{
    public static function run()
    {
        $nowDir           = __DIR__;
        $workerPath       = $nowDir . DIRECTORY_SEPARATOR . 'Worker.php';
        $openSwooleConfig = Factories::config('OpenSwoole');
        CLI::write(
            sprintf(
                "Swoole http server is started at %s:%d\n",
                $openSwooleConfig->listeningIp,
                $openSwooleConfig->listeningPort
            )
        );

        $start = popen("php {$workerPath}", 'w');
        pclose($start);
    }
}
