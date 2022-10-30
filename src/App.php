<?php

namespace Monken\CIBurner;

use CodeIgniter\Config\Services;
use Exception;
use Kint\Kint;
use Monken\CIBurner\Bridge\Debug\Exceptions;
use Monken\CIBurner\Bridge\Debug\Toolbar;
use Monken\CIBurner\Bridge\HandleDBConnection;
use Monken\CIBurner\Bridge\RequestHandler;
use Monken\CIBurner\Bridge\ResponseBridge;
use Monken\CIBurner\Bridge\UploadedFileBridge;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class App
{
    /**
     * run ci4 app
     */
    public static function run(ServerRequestInterface $request, bool $isWebsocket = false): ResponseInterface|bool
    {
        // handle request object
        try {
            Services::reset(true);
            $ci4Request = RequestHandler::initRequest($request, 'workerman');
        } catch (Throwable $e) {
            dump((string) $e);
        }

        // handle debug-bar
        try {
            if (ENVIRONMENT === 'development') {
                Kint::$mode_default_cli = null;
                $toolbar                = new Toolbar(config('Toolbar'), $ci4Request);
                if ($ci4BarResponse = $toolbar->respond()) {
                    $response = new ResponseBridge($ci4BarResponse, $request);

                    return $response;
                }
            }
        } catch (Throwable $e) {
            dump((string) $e . PHP_EOL);
        }

        // run framework and error handling
        try {
            if (! env('CIROAD_DB_AUTOCLOSE')) {
                HandleDBConnection::reconnect();
            }
            $app            = \Config\Services::codeigniter();
            $GLOBALS['app'] = &$app;
            $app->initialize();
            $app->setContext('web')->setRequest($ci4Request)->run(returnResponse: true);
            if ($isWebsocket) {
                return true;
            }
            $ci4Response = Services::response();
        } catch (Throwable $e) {
            if ($isWebsocket) {
                dump($e);

                return false;
            }
            $exception = new Exceptions($request);
            $response  = $exception->exceptionHandler($e);
            unset($app);

            return $response;
        }

        // handle response object
        try {
            // Application code logic
            $response = new ResponseBridge($ci4Response, $request);
            unset($app);

            return $response;
        } catch (Exception $e) {
            return new Response(500, [], 'Something Went Wrong!');
        }

        return $response;
    }

    public static function clean()
    {
        $input = fopen('php://input', 'wb');
        fwrite($input, '');
        fclose($input);

        try {
            if (ob_get_level() !== 0) {
                ob_flush();
                ob_end_clean();
            }
        } catch (Throwable $th) {
        }
        Services::reset(true);
        UploadedFileBridge::reset();
        if (env('CIROAD_DB_AUTOCLOSE')) {
            HandleDBConnection::closeConnect();
        }
    }
}
