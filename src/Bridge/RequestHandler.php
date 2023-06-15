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
        $appConfig       = new App();
        self::$_rRequest = $rRequest;
        self::setFile();

        Services::createRequest($appConfig, false);
        Services::request()->getUserAgent()->parse(self::$_rRequest->getHeaderLine('User-Agent'));

        UriBridge::setUri(self::$_rRequest->getUri());

        Services::request()->setBody(self::getBody());

        self::setParams();
        self::setHeader();
        Services::request()->detectLocale($appConfig);

        return Services::request();
    }

    protected static function setFile()
    {
        if (self::$_rRequest->getUploadedFiles() !== []) {
            $fixedFiles = self::$_rRequest->getUploadedFiles();
            if(BURNER_DRIVER == 'RoadRunner'){
                $fixedFiles = self::reverserPsr7UploadedArrayToFilesArray($fixedFiles);
            }
            $_FILES = self::reverseFixedFilesArray($fixedFiles);

        }
    }

    protected static function reverserPsr7UploadedArrayToFilesArray(array $psr7FileArray): array
    {
        $result = [];
        foreach ($psr7FileArray as $name => $data) {
            if ($data instanceof \Nyholm\Psr7\UploadedFile) {
                $result[$name] = [
                    'name'     => $data->getClientFilename(),
                    'type'     => $data->getClientMediaType(),
                    'tmp_name' => $data->getStream()->getMetadata('uri'),
                    'error'    => $data->getError(),
                    'size'     => $data->getSize(),
                ];
            } else {
                $result[$name] = self::reverserPsr7UploadedArrayToFilesArray($data);
            }
        }
        return $result;
    }

    protected static function reverseFixedFilesArray(array $fixedFilesArray): array
    {
        $output = [];
        foreach ($fixedFilesArray as $name => &$array) {
            foreach ($array as $field => $value) {
                $pointer = &$output[$name];
                
                if (!is_array($value)) {
                    if($field == 'tmp_name'){
                        $value = realpath($value);
                    }
                    $pointer[$field] = $value;
                    continue;
                } else {
                    $value['tmp_name'] = realpath($value['tmp_name']);
                }

                $stack    = [&$pointer];
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveArrayIterator($value),
                    \RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($iterator as $key => $val) {
                    array_splice($stack, $iterator->getDepth() + 1);
                    $pointer = &$stack[count($stack) - 1];
                    $pointer = &$pointer[$key];
                    $stack[] = &$pointer;

                    if (!$iterator->hasChildren()) {
                        $pointer[$field] = $val;
                    }
                }
            }
        }

        return $output;
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
