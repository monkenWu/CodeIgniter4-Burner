<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class RestTest extends CIUnitTestCase
{
    public function testList()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $response = $client->get('/TestRest');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testShow()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $id       = uniqid();
        $response = $client->get("/TestRest/{$id}");
        $this->assertSame(200, $response->getStatusCode());
        $getJson = json_decode($response->getBody(), true);
        $this->assertSame($id, $getJson['id']);
    }

    public function testCreate()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $text1    = uniqid();
        $text2    = uniqid();
        $verify   = md5($text1 . $text2);
        $response = $client->post('/TestRest', [
            'http_errors' => false,
            'json'        => [
                'text1' => $text1,
                'text2' => $text2,
            ],
        ]);
        $this->assertSame(201, $response->getStatusCode());
        $getJson   = json_decode($response->getBody(), true);
        $resVerify = md5($getJson['data']['text1'] . $getJson['data']['text2']);
        $this->assertSame($verify, $resVerify);
    }

    public function testUpdate()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $text1    = uniqid();
        $text2    = uniqid();
        $verify   = md5($text1 . $text2);
        $id       = uniqid();
        $response = $client->put("/TestRest/{$id}", [
            'http_errors' => false,
            'json'        => [
                'text1' => $text1,
                'text2' => $text2,
            ],
        ]);
        $this->assertSame(200, $response->getStatusCode());
        $getJson   = json_decode($response->getBody(), true);
        $resVerify = md5($getJson['data']['text1'] . $getJson['data']['text2']);
        $this->assertSame($verify, $resVerify);
        $this->assertSame($id, $getJson['id']);
    }

    public function testNew()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $response = $client->get('/TestRest/new');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('newView', $response->getBody());
    }

    public function testEdit()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $id       = uniqid();
        $response = $client->get("/TestRest/{$id}/edit");
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($id . 'editView', $response->getBody());
    }

    public function testDelete()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $id       = uniqid();
        $response = $client->delete("/TestRest/{$id}");
        $this->assertSame(200, $response->getStatusCode());
        $getJson = json_decode($response->getBody(), true);
        $this->assertSame($id, $getJson['id']);
    }
}
