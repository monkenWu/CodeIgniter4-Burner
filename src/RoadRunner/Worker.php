<?php

require_once realpath(__DIR__ . '/../FrontLoader.php');

define('BURNER_DRIVER', 'RoadRunner');

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker;

/**
 * RoadRunner worker init
 */
$worker     = Worker::create();
$psrFactory = new Psr17Factory();
$psr7       = new PSR7Worker($worker, $psrFactory, $psrFactory, $psrFactory);

while (true) {
    // get psr7 request
    try {
        $request = $psr7->waitRequest();
        if (! ($request instanceof RequestInterface)) { // Termination request received
            break;
        }
    } catch (Exception $e) {
        $psr7->respond(new Response(400)); // Bad Request

        continue;
    }

    /** @var \Psr\Http\Message\ResponseInterface */
    $response = \Monken\CIBurner\App::run($request);

    // handle response object
    try {
        $psr7->respond($response);
        \Monken\CIBurner\App::clean();
    } catch (Exception $e) {
        $psr7->respond(new Response(500, [], 'Something Went Wrong!'));
    }
}
