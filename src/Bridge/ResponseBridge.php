<?php

namespace Monken\CIBurner\Bridge;

use Config\App;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\InjectContentTypeTrait;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class ResponseBridge extends Response
{
    use InjectContentTypeTrait;

    private $_rRequest;

    public function __construct(
        \CodeIgniter\HTTP\Response $ci4Response,
        ServerRequestInterface $rRequest
    ) {
        $this->_rRequest = $rRequest;

        parent::__construct(
            $this->createBody($ci4Response),
            $this->getCi4StatusCode($ci4Response),
            $this->getCi4Headers($ci4Response)
        );
    }

    private function getCi4ContentType(\CodeIgniter\HTTP\Response $ci4Response): string
    {
        return $ci4Response->getHeaderLine('Content-Type');
    }

    private function getCi4Headers(\CodeIgniter\HTTP\Response $ci4Response): array
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $sessionID        = session_id();
            $sessionName      = session_name();
            $cookiesSessionID = $this->_rRequest->getCookieParams()[$sessionName] ?? '';
            $cookiesParams    = session_get_cookie_params();
            $config           = config(App::class);

            if ($cookiesSessionID === '') {
                $cookieStr = $this->getCookieString(
                    $sessionName,
                    $sessionID,
                    (time() + $cookiesParams['lifetime']),
                    $cookiesParams['path'],
                    $cookiesParams['domain'],
                    $cookiesParams['secure'],
                    $cookiesParams['httponly']
                );
                $ci4Response->setHeader('Set-Cookie', $cookieStr);
            } elseif ($cookiesSessionID !== $sessionID) {
                $cookieStr = $this->getCookieString(
                    $sessionName,
                    '',
                    time(),
                    $config->cookiePath,
                    $config->cookieDomain,
                    $config->cookieSecure,
                    $config->cookieHTTPOnly
                );
                $ci4Response->setHeader('Set-Cookie', $cookieStr);
            }

            unset($_SESSION);
            session_write_close();
            session_id(null);
        }

        $ci4headers = $ci4Response->headers();
        $headers    = [];

        foreach ($ci4headers as $key => $value) {
            $headers[$key] = $value->getValueLine();
        }

        return $headers;
    }

    private function getCookieString($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true)
    {
        $str = urlencode($name) . '=';
        $str .= urlencode($value);

        if ($expire !== 0) {
            $str .= '; Expires=' . gmdate('D, d-M-Y H:i:s T', $expire);
        }
        if ($path) {
            $str .= '; Path=' . $path;
        }
        if ($domain) {
            $str .= '; Domain=' . $domain;
        }
        if (true === $secure) {
            $str .= '; Secure';
        }
        if (true === $httpOnly) {
            $str .= '; HttpOnly';
        }

        return $str;
    }

    private function getcookie($name)
    {
        $cookies = [];
        $headers = headers_list();

        foreach ($headers as $header) {
            if (strpos($header, 'Set-Cookie: ') === 0) {
                $value = str_replace('&', urlencode('&'), substr($header, 12));
                parse_str(current(explode(';', $value, 1)), $pair);
                $cookies = array_merge_recursive($cookies, $pair);
            }
        }

        return $cookies[$name] ?? false;
    }

    private function getCi4StatusCode(\CodeIgniter\HTTP\Response $ci4Response): int
    {
        return $ci4Response->getStatusCode();
    }

    private function createBody(\CodeIgniter\HTTP\Response $ci4Response): StreamInterface
    {
        $html = $ci4Response->getBody();
        if ($html instanceof StreamInterface) {
            return $html;
        } else if(is_null($html)) {
            $html = '';
        }
        $body = new Stream('php://temp', 'wb+');
        $body->write($html);
        $body->rewind();

        return $body;
    }
}
