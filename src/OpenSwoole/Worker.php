<?php

namespace Monken\CIBurner\OpenSwoole;

require_once realpath(__DIR__ . '/../FrontLoader.php');

define('BURNER_DRIVER', 'OpenSwoole');

use CodeIgniter\Config\Factories;
use CodeIgniter\Events\Events;
use Exception;
use Imefisto\PsrSwoole\ResponseMerger;
use Imefisto\PsrSwoole\ServerRequest as PsrRequest;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as HttpServer;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class Worker
{
    protected static Psr17Factory $uriFactory;
    protected static Psr17Factory $streamFactory;
    protected static Psr17Factory $uploadedFileFactory;
    protected static ResponseMerger $responseMerger;
    protected static HttpServer|WebSocketServer $server;
    protected static ?Frame $frame = null;

    /**
     * Init Worker
     *
     * @return void
     */
    public static function init(HttpServer|WebSocketServer $server)
    {
        self::$uriFactory          = new Psr17Factory();
        self::$streamFactory       = new Psr17Factory();
        self::$uploadedFileFactory = new Psr17Factory();
        self::$responseMerger      = new ResponseMerger();
        self::$server              = $server;
    }

    /**
     * get OpenSwoole Server Instance
     *
     * @return \Swoole\Http\Server|Swoole\WebSocket\Server
     */
    public static function getServer(): HttpServer|WebSocketServer
    {
        return self::$server;
    }

    /**
     * get OpenSwoole Websocket-Frame Instance
     */
    public static function getFrame(): Frame
    {
        if (self::$frame === null) {
            throw new Exception('You must start the burner through websocketProcesser to get the Frame instance.');
        }

        return self::$frame;
    }

    /**
     * Burner handles CodeIgniter4 entry points
     * and will automatically execute the Swoole-Server-end Sending Response.
     *
     * @return void
     */
    public static function httpProcesser(Request $swooleRequest, Response $swooleResponse)
    {
        $response = \Monken\CIBurner\App::run(self::requestFactory($swooleRequest));

        self::$responseMerger->toSwoole(
            $response,
            $swooleResponse
        )->end();

        Events::trigger('burnerAfterSendResponse', self::$server);

        \Monken\CIBurner\App::clean();
    }

    public static function websocketProcesser(Request $swooleRequest, Frame $frame)
    {
        self::$frame = $frame;
        \Monken\CIBurner\App::run(self::requestFactory($swooleRequest));
        \Monken\CIBurner\App::clean();
    }

    public static function websocketPush($data, int $opcode = 1)
    {
        self::$server->push(self::$frame->fd, $data, $opcode);
        Events::trigger('burnerAfterPushMessage', self::$server);
    }

    /**
     * Convert Swoole-Request to psr7-Request
     */
    public static function requestFactory(Request $swooleRequest): ServerRequestInterface
    {
        if (null === $swooleRequest->files) {
            $swooleRequest->files = [];
        }

        return (new PsrRequest(
            $swooleRequest,
            self::$uriFactory,
            self::$streamFactory,
            self::$uploadedFileFactory
        ))->withUploadedFiles($swooleRequest->files);
    }
}

/** @var \Config\OpenSwoole */
$openSwooleConfig = Factories::config('OpenSwoole');
$server           = new ($openSwooleConfig->httpDriver)(
    $openSwooleConfig->listeningIp,
    $openSwooleConfig->listeningPort,
    $openSwooleConfig->mode,
    $openSwooleConfig->type
);
Worker::init($server);
$server->set($openSwooleConfig->config);
$openSwooleConfig->server($server);
$server->start();
