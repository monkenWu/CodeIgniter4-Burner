<?php

namespace Monken\CIBurner;

use Closure;
use CodeIgniter\Config\BaseService;
use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use Config\Autoload;
use Config\Burner as BurnerConfig;
use Config\Modules;
use Exception;
use Kint\Kint;
use Monken\CIBurner\Bridge\Debug\Exceptions;
use Monken\CIBurner\Bridge\Debug\Toolbar;
use Monken\CIBurner\Bridge\HandleConnections;
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
     * Burner Config Instance
     */
    protected static BurnerConfig $config;

    /**
     * Set burner config
     *
     * @return void
     */
    public static function setConfig(BurnerConfig $config)
    {
        self::$config = $config;
    }

    /**
     * run ci4 app
     */
    public static function run(ServerRequestInterface $request, bool $isWebsocket = false): ResponseInterface|bool
    {
        // handle request object
        try {
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
            HandleConnections::reconnect(self::$config);
            $app            = \Config\Services::codeigniter();
            $GLOBALS['app'] = &$app;
            $app->initialize();
            $app->setContext('web')->setRequest($ci4Request)->run(returnResponse: true);
            if ($isWebsocket) {
                return true;
            }
            $ci4Response = Services::response();
        } catch (Throwable $e) {
            $exception = new Exceptions($request);
            $response  = $exception->exceptionHandler($e);
            unset($app);

            if ($isWebsocket) {
                return false;
            }

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
        self::resetServices();
        Factories::reset();
        HandleConnections::close(self::$config);
        unset($_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['HTTP_X_REAL_IP'], $_SERVER['HTTP_USER_AGENT']);
        UploadedFileBridge::reset();
    }

    /**
     * Initialize all instances in the service after the HTTP response
     * to prevent already used singletons from affecting the next request.
     *
     * @return void
     */
    public static function resetServices()
    {
        $reseter = Closure::bind(function (array $skipInitServices) {
            $unsetServices = [];

            foreach (self::$instances as $serviceName => $instance) {
                if (in_array($serviceName, $skipInitServices, true) === false) {
                    $unsetServices[] = $serviceName;
                }
            }

            foreach ($unsetServices as $name) {
                unset(self::$mocks[$name], self::$instances[$name]);
            }
            self::autoloader()->initialize(new Autoload(), new Modules());
        }, new BaseService(), BaseService::class);
        $skipInitServices = self::$config->skipInitServices;
        $skipInitServices[] = 'cache';
        $reseter($skipInitServices);
    }
}
