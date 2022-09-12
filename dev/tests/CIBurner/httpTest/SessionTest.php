<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class SessionTest extends CIUnitTestCase
{
    public function testSession()
    {
        for ($i = 0; $i < 3; $i++) {
            //set session
            $client = Services::curlrequest([
                'base_uri' => 'http://localhost:8080/',
            ], null, null, false);
            $checkText = uniqid();
            $response  = $client->post('/sessionTest/createdSession', [
                'form_params' => [
                    'text' => $checkText,
                ],
                'http_errors' => false,
            ]);
            $this->assertSame(201, $response->getStatusCode());
            //check session
            $setCookie = $response->getHeaders()['Set-Cookie']->getValue();
            $session   = explode('=', explode(';', $setCookie)[0]);
            $response  = $client->get('/sessionTest/getSessionText', [
                'headers' => [
                    'Cookie' => "{$session[0]}={$session[1]}",
                ],
            ]);
            $this->assertSame(200, $response->getStatusCode());
            $getServerCheckText = json_decode($response->getBody(), true)['text'];
            $this->assertSame($checkText, $getServerCheckText);
        }
    }
}
