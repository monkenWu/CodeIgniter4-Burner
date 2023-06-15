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

    public function testSetCookies()
    {
        $text1 = md5(uniqid().'text1');
        $text2 = md5(uniqid().'text2');
        $text3 = md5(uniqid().'text3');
        $query = http_build_query([
            'text1' => $text1,
            'text2' => $text2,
            'text3' => $text3,
        ]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/basicTest/cookieCreate?' . $query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        //get url query
        $output = curl_exec($ch);
        curl_close($ch);
        $cookies = [];
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $output, $matches);
        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        
        foreach ($cookies as $key => $value) {
            $this->assertSame(${$key}, $value);
        }
    }

    public function testCsrfFormParams()
    {
        //config
        config('Security');
        $tokenName = config('Security')->tokenName;
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);

        //get csrf key
        $getCsrfKey = function() use ($client){
            $response = $client->get('/basicTest/csrfCreate');
            $this->assertSame(200, $response->getStatusCode());
            $setCookie = $response->getHeaderLine('Set-Cookie');
            $csrf   = explode('=', explode(';', $setCookie)[0]);
            return $csrf;
        };

        //csrf form teset
        $csrf = $getCsrfKey();
        $text1 = md5(uniqid().'text1');
        $response = $client->post('basicTest/csrfVerify', [
            'headers' => [
                'Cookie' => "{$csrf[0]}={$csrf[1]}",
            ],
            'form_params' => [
                $tokenName => $csrf[1],
                'text1' => $text1,
            ],
        ]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($text1, $response->getBody());
    }

    public function testCsrfFormHeader()
    {
        //config
        config('Security');
        $headerName = config('Security')->headerName;
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);

        //get csrf key
        $getCsrfKey = function() use ($client){
            $response = $client->get('/basicTest/csrfCreate');
            $this->assertSame(200, $response->getStatusCode());
            $setCookie = $response->getHeaderLine('Set-Cookie');
            $csrf   = explode('=', explode(';', $setCookie)[0]);
            return $csrf;
        };

        //csrf header test
        $csrf = $getCsrfKey();
        $text1 = md5(uniqid().'text1');
        $response = $client->post('basicTest/csrfVerify', [
            'headers' => [
                'Cookie' => "{$csrf[0]}={$csrf[1]}",
                $headerName => $csrf[1],
            ],
            'form_params' => [
                'text1' => $text1,
            ],
        ]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($text1, $response->getBody());
    }

}
