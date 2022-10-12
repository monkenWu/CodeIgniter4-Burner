<?php

$pathsConfig = '';
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . '../../../../autoload.php')) {
    require_once realpath(__DIR__ . DIRECTORY_SEPARATOR . '../../../../autoload.php');
    if (! defined('FCPATH')) {
        define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
    }
    chdir(FCPATH);
    $pathsConfig = realpath(FCPATH . '../../../../../app/Config/Paths.php');
} elseif (file_exists(__DIR__ . DIRECTORY_SEPARATOR . '../../dev/vendor/autoload.php')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . '../../dev/vendor/autoload.php';
    if (! defined('FCPATH')) {
        define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
    }
    chdir(FCPATH);
    $pathsConfig = realpath(FCPATH . '../../dev/app/Config/Paths.php');
}
define('BURNER_DRIVER', 'OpenSwoole');

use CodeIgniter\Config\Factories;
use Config\Paths;
use Imefisto\PsrSwoole\ResponseMerger;
use Imefisto\PsrSwoole\ServerRequest as PsrRequest;
use Nyholm\Psr7\Factory\Psr17Factory;
use Swoole\Http\Request;
use Swoole\Http\Response;

// CodeIgniter4 init
// Override is_cli()
if (! function_exists('is_cli')) {
    function is_cli(): bool
    {
        return false;
    }
}

// // Ci4 4.2.0 init
require_once realpath($pathsConfig) ?: $pathsConfig;
$paths     = new Paths();
$botstorap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once realpath($botstorap);
require_once SYSTEMPATH . 'Config/DotEnv.php';
(new \CodeIgniter\Config\DotEnv(ROOTPATH))->load();
$app = \Config\Services::codeigniter();
$app->initialize();
$app->setContext('web');

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
