<?php

namespace Monken\CIBurner\Bridge;

use CodeIgniter\Config\Services;
use Config\App;
use Psr\Http\Message\ServerRequestInterface;

class RequestHandler
{
    /**
     * RoadRunner Request
     */
    public static ServerRequestInterface $_rRequest;

    public static function initRequest(ServerRequestInterface $rRequest)
    {
        self::$_rRequest = $rRequest;
        self::setFile();

        $_SERVER['HTTP_USER_AGENT'] = self::$_rRequest->getHeaderLine('User-Agent');

        Services::createRequest(new App(), false);
        Services::request()->getUserAgent()->parse($_SERVER['HTTP_USER_AGENT']);

        UriBridge::setUri(self::$_rRequest->getUri());

        // $rRequest->g
        Services::request()->setBody(self::getBody());

        self::setParams();
        self::setHeader();

        return Services::request();
    }

    protected static function setFile()
    {
        if (self::$_rRequest->getUploadedFiles() !== []) {
            UploadedFileBridge::getPsr7UploadedFiles(self::$_rRequest->getUploadedFiles(), true);
        }
    }

    protected static function getBody()
    {
        if (strpos(self::$_rRequest->getHeaderLine('content-type'), 'application/json') === 0) {
            return self::$_rRequest->getBody();
        }

        return self::$_rRequest->getBody()->getContents();
    }

    protected static function setParams()
    {
        Services::request()->setMethod(self::$_rRequest->getMethod());
        Services::request()->setGlobal('get', self::$_rRequest->getQueryParams());

        if (self::$_rRequest->getMethod() === 'POST') {
            Services::request()->setGlobal('post', self::$_rRequest->getParsedBody());
        }

        $_COOKIE = [];
        Services::request()->setGlobal('cookie', self::$_rRequest->getCookieParams());

        foreach (self::$_rRequest->getCookieParams() as $key => $value) {
            $_COOKIE[$key] = $value;
        }

        if (isset($_COOKIE[config(App::class)->sessionCookieName])) {
            session_id($_COOKIE[config(App::class)->sessionCookieName]);
        }

        Services::request()->setGlobal('server', self::$_rRequest->getServerParams());
    }

    protected static function setHeader()
    {
        $rHeader = self::$_rRequest->getHeaders();

        foreach ($rHeader as $key => $datas) {
            if (is_string($datas)) {
                Services::request()->setHeader($key, $datas);
            }
            if (is_array($datas)) {
                foreach ($datas as $values) {
                    Services::request()->setHeader($key, $values);
                }
            }
        }
    }
}
