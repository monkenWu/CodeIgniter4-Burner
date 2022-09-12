<?php

if (file_exists('./vendor/autoload.php')) {
    require_once './vendor/autoload.php';
    define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
    chdir(FCPATH);
    $pathsConfig = realpath(FCPATH . '../../../../app/Config/Paths.php');
} elseif (file_exists('../../dev/vendor/autoload.php')) {
    require_once '../../dev/vendor/autoload.php';
    define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
    chdir(FCPATH);
    $pathsConfig = realpath(FCPATH . '../../dev/app/Config/Paths.php');
}
require_once 'FileMonitor.php';
define('BURNER_DRIVER', 'WorkerMan');

use Config\Paths;
use Nyholm\Psr7\ServerRequest as PsrRequest;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;

$webWorker             = new Worker('http://0.0.0.0:8080');
$webWorker->reloadable = true;
$webWorker->name       = 'CodeIgniter4';

// CodeIgniter4 init
// Override is_cli()
if (! function_exists('is_cli')) {
    function is_cli(): bool
    {
        return false;
    }
}

$webWorker->onWorkerStart = function () use ($pathsConfig) {
    //Ci4 4.2.0 init
    require realpath($pathsConfig) ?: $pathsConfig;
    $paths     = new Paths();
    $botstorap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
    require realpath($botstorap);
    require_once SYSTEMPATH . 'Config/DotEnv.php';
    (new \CodeIgniter\Config\DotEnv(ROOTPATH))->load();
    $app = \Config\Services::codeigniter();
    $app->initialize();
    $app->setContext('web');
};

//Worker 進入點
$webWorker->onMessage = static function (TcpConnection $connection, Request $request) {
    //Static File
    $response = \Monken\CIBurner\Workerman\StaticFile::withFile($request);
    if ((null === $response) === false) {
        $connection->send($response);

        return;
    }

    //init psr7 request
    $_SERVER['HTTP_USER_AGENT'] = $request->header('User-Agent');
    $psrRequest                 = (new PsrRequest(
        $request->method(),
        $request->uri(),
        $request->header(),
        $request->rawBody(),
        $request->protocolVersion(),
        $_SERVER
    ))->withQueryParams($request->get())
        ->withCookieParams($request->cookie())
        ->withParsedBody($request->post() ?? [])
        ->withUploadedFiles($request->file() ?? []);
    unset($request);

    //process response
    if ($response === null) {
        /** @var \Psr\Http\Message\ResponseInterface */
        $response = \Monken\CIBurner\App::run($psrRequest);
    }

    $workermanResponse = new Response(
        $response->getStatusCode(),
        $response->getHeaders(),
        $response->getBody()->getContents()
    );

    $connection->send($workermanResponse);
    \Monken\CIBurner\App::clean();
};

Worker::runAll();
