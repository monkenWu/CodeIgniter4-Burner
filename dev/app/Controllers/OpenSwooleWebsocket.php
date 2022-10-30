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
        $nowUserFd = Worker::getFrame()->fd;
        $data      = Worker::getFrame()->data;
        Worker::websocketPush('Controller Get Message!');
        Worker::websocketPushAll(static function (int $fd) use ($nowUserFd, $data) {
            if ($fd === $nowUserFd) {
                return sprintf('You(%d) say: %s', $fd, $data);
            }

            return sprintf('%d says: %s', $nowUserFd, $data);
        });
    }
}
