<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

final class FileUploadTest extends CIUnitTestCase
{
    
    public function testPsrFileUpload()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $dir      = __DIR__ . DIRECTORY_SEPARATOR . 'testFiles' . DIRECTORY_SEPARATOR;
        $upload1  = $dir . 'upload1.text';
        $upload2  = $dir . 'upload2.text';
        $response = $client->post('/FileUploadTest/psrFileUpload', [
            'multipart' => [
                'upload1' => new CURLFile($upload1, 'text/plain', 'upload1.text'),
                'upload2' => new CURLFile($upload2, 'text/plain', 'upload2.text'),
            ],
        ]);
        $this->assertSame(201, $response->getStatusCode());
        $getServerMD5Text = json_decode($response->getBody(), true);
        $this->assertTrue(
            $getServerMD5Text['upload1.text'] === md5_file($upload1)
            && $getServerMD5Text['upload2.text'] === md5_file($upload2)
        );
    }

    public function testPsrFileMultipleUpload()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $dir      = __DIR__ . DIRECTORY_SEPARATOR . 'testFiles' . DIRECTORY_SEPARATOR;
        $upload1  = $dir . 'upload1.text';
        $upload2  = $dir . 'upload2.text';
        $response = $client->post('/FileUploadTest/psrFileMultipleUpload', [
            'multipart' => [
                'data[0]' => new CURLFile($upload1, 'text/plain', 'upload1.text'),
                'data[1]' => new CURLFile($upload2, 'text/plain', 'upload2.text'),
            ],
        ]);    
        $this->assertSame(201, $response->getStatusCode());
        $getServerMD5Text = json_decode($response->getBody(), true);
        $this->assertTrue(
            $getServerMD5Text['upload1.text'] === md5_file($upload1)
            && $getServerMD5Text['upload2.text'] === md5_file($upload2)
        );
    }

    /**
     * @group testFileUpload
     */
    public function testFileUpload()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $dir      = __DIR__ . DIRECTORY_SEPARATOR . 'testFiles' . DIRECTORY_SEPARATOR;
        $upload1  = $dir . 'upload1.text';
        $upload2  = $dir . 'upload2.text';
        $response = $client->post('/FileUploadTest/fileUpload', [
            'multipart' => [
                'upload1' => new CURLFile($upload1, 'text/plain', 'upload1.text'),
                'upload2' => new CURLFile($upload2, 'text/plain', 'upload2.text'),
            ],
        ]);
        $this->assertSame(201, $response->getStatusCode());
        $getServerMD5Text = json_decode($response->getBody(), true);
        $this->assertTrue(
            $getServerMD5Text['upload1.text'] === md5_file($upload1)
            && $getServerMD5Text['upload2.text'] === md5_file($upload2)
        );
    }

    public function testFileMultipleUpload()
    {
        $client = Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $dir      = __DIR__ . DIRECTORY_SEPARATOR . 'testFiles' . DIRECTORY_SEPARATOR;
        $upload1  = $dir . 'upload1.text';
        $upload2  = $dir . 'upload2.text';
        $response = $client->post('/FileUploadTest/fileMultipleUpload', [
            'multipart' => [
                'data[0]' => new CURLFile($upload1, 'text/plain', 'upload1.text'),
                'data[1]' => new CURLFile($upload2, 'text/plain', 'upload2.text'),
            ],
        ]);
        $this->assertSame(201, $response->getStatusCode());
        $getServerMD5Text = json_decode($response->getBody(), true);
        $this->assertTrue(
            $getServerMD5Text['upload1.text'] === md5_file($upload1)
            && $getServerMD5Text['upload2.text'] === md5_file($upload2)
        );
    }

}
