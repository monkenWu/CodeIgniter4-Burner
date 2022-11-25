<?php

namespace App\Controllers;

use Monken\CIBurner\OpenSwoole\Worker;

class OpenSwooleWebsocket extends BaseController
{
    public function index()
    {
        return view('swoole_websocket');
    }

    public function socket()
    {
        $frameData = Worker::getFrame();
        $nowUserFd = $frameData->fd;
        $data      = $frameData->data;
        $workerId  = Worker::getServer()->worker_id;
        $processId = Worker::getServer()->worker_pid;
        Worker::push(sprintf('Controller Get Message! fd: %d, workerId: %d, processId: %d', $nowUserFd, $workerId, $processId), $nowUserFd);
        Worker::pushAll(static function (int $fd) use ($nowUserFd, $data) {
            if ($fd === $nowUserFd) {
                return sprintf('You(%d) say: %s', $fd, $data);
            }

            return sprintf('%d says: %s', $nowUserFd, $data);
        });
    }
}
