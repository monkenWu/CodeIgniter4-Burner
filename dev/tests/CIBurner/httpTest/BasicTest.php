<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class BasicTest extends CIUnitTestCase
{
    public function testLoadView()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $response = $client->get('/basicTest/loadView');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testEchoText()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $response = $client->get('/basicTest/echoText');
        $this->assertSame('testText', $response->getBody());
    }

    public function testUrlQuery()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $text1    = uniqid();
        $text2    = uniqid();
        $text3    = uniqid();
        $verify   = md5($text1 . $text2 . $text3);
        $response = $client->get('/basicTest/urlqyery', [
            'query' => [
                'texts' => [$text1, $text2],
                'text3' => $text3,
            ],
        ]);
        $this->assertSame($verify, $response->getBody());
    }

    public function testFormParams()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $text1    = uniqid();
        $text2    = uniqid();
        $text3    = uniqid();
        $verify   = md5($text1 . $text2 . $text3);
        $response = $client->post('/basicTest/formparams', [
            'form_params' => [
                'texts' => [$text1, $text2],
                'text3' => $text3,
            ],
        ]);
        $this->assertSame($verify, $response->getBody());
    }

    public function testFormParamsAndQuery()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $text1    = uniqid();
        $text2    = uniqid();
        $verify   = md5($text1 . $text2);
        $response = $client->post('/basicTest/formparamsandquery', [
            'query' => [
                'text1' => $text1,
            ],
            'form_params' => [
                'text2' => $text2,
            ],
        ]);
        $this->assertSame($verify, $response->getBody());
    }

    public function testReadHeader()
    {
        for ($i = 0; $i < 2; $i++) {
            $client = Services::curlrequest([
                'base_uri' => 'http://localhost:8080/',
            ], null, null, false);
            $token    = uniqid();
            $response = $client->get('/basicTest/readHeader', [
                'headers' => [
                    'X-Auth-Token' => $token,
                ],
            ]);
            $this->assertSame(200, $response->getStatusCode());
            $getServerCheckText = json_decode($response->getBody(), true)['X-Auth-Token'];
            $this->assertSame($token, $getServerCheckText);
        }
    }

    public function testSendHeader()
    {
        $tokens = [];

        for ($i = 0; $i < 2; $i++) {
            $client = Services::curlrequest([
                'base_uri' => 'http://localhost:8080/',
            ], null, null, false);
            $token    = uniqid();
            $response = $client->get('/basicTest/sendHeader');
            $this->assertSame(200, $response->getStatusCode());
            $tokens[] = $response->getHeader('X-Set-Auth-Token')->getValueLine();
        }
        $this->assertNotSame($token[1], $tokens[0]);
    }

    public function testI18n()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);

        $response = $client->get('/basicTest/i18n', [
            'headers' => [
                'Accept-Language' => 'en-US;q=0.8,en;q=0.7',
            ],
        ]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('english', $response->getBody());

        $response = $client->get('/basicTest/i18n', [
            'headers' => [
                'Accept-Language' => 'zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7',
            ],
        ]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('正體中文', $response->getBody());
    }
}
