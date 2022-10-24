<?php

namespace Monken\CIBurner\OpenSwoole;

require_once realpath(__DIR__ . '/../FrontLoader.php');

define('BURNER_DRIVER', 'OpenSwoole');

use CodeIgniter\Config\Factories;
use Imefisto\PsrSwoole\ResponseMerger;
use Imefisto\PsrSwoole\ServerRequest as PsrRequest;
use Nyholm\Psr7\Factory\Psr17Factory;
use Swoole\Http\Request;
use Swoole\Http\Response;

class Worker
{
    protected static $uriFactory;
    protected static $streamFactory;
    protected static $uploadedFileFactory;
    protected static $responseMerger;
    private static $init = false;

    protected static function init()
    {
        self::$uriFactory          = new Psr17Factory();
        self::$streamFactory       = new Psr17Factory();
        self::$uploadedFileFactory = new Psr17Factory();
        self::$responseMerger      = new ResponseMerger();
    }

    /**
     * Burner handles CodeIgniter4 entry points
     * and will automatically execute the Swoole-Server-end Sending Response.
     *
     * @return void
     */
    public static function mainProcesser(Request $swooleRequest, Response $swooleResponse)
    {
        if (self::$init === false) {
            self::init();
        }
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
$server->set($openSwooleConfig->config);
$openSwooleConfig->server($server);
$server->start();
