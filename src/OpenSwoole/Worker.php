<?php

require_once realpath(__DIR__ . '/../FrontLoader.php');

define('BURNER_DRIVER', 'OpenSwoole');

use CodeIgniter\Config\Factories;
use Imefisto\PsrSwoole\ResponseMerger;
use Imefisto\PsrSwoole\ServerRequest as PsrRequest;
use Nyholm\Psr7\Factory\Psr17Factory;
use Swoole\Http\Request;
use Swoole\Http\Response;

/** @var \Config\OpenSwoole */
$openSwooleConfig = Factories::config('OpenSwoole');
$server           = new Swoole\HTTP\Server(
    $openSwooleConfig->listeningIp,
    $openSwooleConfig->listeningPort,
    $openSwooleConfig->mode
);
$server->set($openSwooleConfig->config);
$openSwooleConfig->initServer($server);
$uriFactory          = new Psr17Factory();
$streamFactory       = new Psr17Factory();
$uploadedFileFactory = new Psr17Factory();
$responseMerger      = new ResponseMerger();

$server->on('request', static function (Request $swooleRequest, Response $swooleResponse) use ($uriFactory, $streamFactory, $uploadedFileFactory, $responseMerger) {
    if (null === $swooleRequest->files) {
        $swooleRequest->files = [];
    }

    $psrRequest = (new PsrRequest(
        $swooleRequest,
        $uriFactory,
        $streamFactory,
        $uploadedFileFactory
    ))->withUploadedFiles($swooleRequest->files);

    $response = \Monken\CIBurner\App::run($psrRequest);

    $responseMerger->toSwoole(
        $response,
        $swooleResponse
    )->end();

    \Monken\CIBurner\App::clean();
});

$server->start();
