<?php

namespace Monken\CIBurner\OpenSwoole;

require_once realpath(__DIR__ . '/../FrontLoader.php');

define('BURNER_DRIVER', 'OpenSwoole');

use CodeIgniter\Config\Factories;
use CodeIgniter\Events\Events;
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

    /**
     * Websocket Request Pool
     *
     * @var array<string,\Swoole\Http\Request>
     */
    protected static array $websocketRequestPool = [];

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

    /**
     * Please pass the Swoole-Request object in the Swoole Webscoket Open-Event to initialize the worker.
     *
     * @return void
     */
    public static function initializeWebsocket(Request $swooleRequest)
    {
        if (self::$server->isEstablished($swooleRequest->fd)) {
            self::$websocketRequestPool['fd' . $swooleRequest->fd] = $swooleRequest;
        }
    }

    /**
     * Remove connection from pool upon close
     *
     * @return void
     */
    public static function unsetWebsocket(int $fd)
    {
        unset(self::$websocketRequestPool['fd' . $fd]);
    }

    /**
     * Burner handles CodeIgniter4 entry points.
     * Use this function in the Swoole Websocket Message-Event.
     *
     * @return void
     */
    public static function websocketProcesser(Frame $frame)
    {
        if ($websocketRequest = (self::$websocketRequestPool['fd' . $frame->fd] ?? false)) {
            $psr7Request = self::requestFactory($websocketRequest)
                ->withBody(self::$streamFactory->createStream(json_encode($frame)))
                ->withHeader('Content-Type', 'application/json');
            \Monken\CIBurner\App::run($psr7Request, true);
            \Monken\CIBurner\App::clean();
        }
    }

    /**
     * Push message to client.
     *
     * @param mixed    $data
     * @param int|null $fd   If not passed in, it will be pushed to the current fd
     *
     * @return void
     */
    public static function push($data, int $fd, int $opcode = 1)
    {
        if (self::$server->isEstablished($fd)) {
            self::$server->push($fd, $data, $opcode);
            Events::trigger('burnerAfterPushMessage', self::$server, $fd);
        }
    }

    /**
     * Push messages to all client.
     *
     * @param callable $messageProcesser $messageProcesser(int $fd)
     *
     * @return void
     */
    public static function pushAll(callable $messageProcesser, int $opcode = 1)
    {
        foreach (self::$server->connections as $fd) {
            if (self::$server->isEstablished($fd)) {
                $message = $messageProcesser($fd);
                if (null === $message) {
                    continue;
                }
                if (is_array($message)) {
                    self::push($message['message'], $fd, $message['opcode']);
                } else {
                    self::push($message, $fd, $opcode);
                }
            }
        }
        Events::trigger('burnerAfterPushAllMessage', self::$server);
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
