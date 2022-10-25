<?php

namespace Monken\CIBurner\OpenSwoole;

require_once realpath(__DIR__ . '/../FrontLoader.php');

define('BURNER_DRIVER', 'OpenSwoole');

use CodeIgniter\Config\Factories;
use \CodeIgniter\Events\Events;
use Imefisto\PsrSwoole\ResponseMerger;
use Imefisto\PsrSwoole\ServerRequest as PsrRequest;
use Nyholm\Psr7\Factory\Psr17Factory;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

class Worker
{
    protected static Psr17Factory $uriFactory;
    protected static Psr17Factory $streamFactory;
    protected static Psr17Factory $uploadedFileFactory;
    protected static ResponseMerger $responseMerger;
    protected static \Swoole\HTTP\Server|\Swoole\WebSocket\Server $server;

    /**
     * Init Worker
     *
     * @param \Swoole\HTTP\Server|\Swoole\WebSocket\Server $server
     * @return void
     */
    public static function init(\Swoole\HTTP\Server|\Swoole\WebSocket\Server $server)
    {
        self::$uriFactory          = new Psr17Factory();
        self::$streamFactory       = new Psr17Factory();
        self::$uploadedFileFactory = new Psr17Factory();
        self::$responseMerger      = new ResponseMerger();
        self::$server = $server;
    }

    /**
     * get OpenSwoole Server Instance
     *
     * @return \Swoole\HTTP\Server|\Swoole\WebSocket\Server
     */
    public static function getServer(): \Swoole\HTTP\Server|\Swoole\WebSocket\Server
    {
        return self::$server;
    }

    /**
     * Burner handles CodeIgniter4 entry points
     * and will automatically execute the Swoole-Server-end Sending Response.
     *
     * @return void
     */
    public static function mainProcesser(Request $swooleRequest, Response $swooleResponse)
    {
        if (null === $swooleRequest->files) {
            $swooleRequest->files = [];
        }

        $psrRequest = (new PsrRequest(
            $swooleRequest,
            self::$uriFactory,
            self::$streamFactory,
            self::$uploadedFileFactory
        ))->withUploadedFiles($swooleRequest->files);

        $response = \Monken\CIBurner\App::run($psrRequest);

        self::$responseMerger->toSwoole(
            $response,
            $swooleResponse
        )->end();
        
        Events::trigger('burnerAfterSendResponse', self::$server);

        \Monken\CIBurner\App::clean();
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
