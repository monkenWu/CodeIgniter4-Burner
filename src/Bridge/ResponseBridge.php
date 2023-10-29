<?php

namespace Monken\CIBurner\Bridge;

use Config\Cookie;
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
        $setCookiesArray = $this->ci4Cookies($ci4Response);
        $ci4headers = $ci4Response->headers();
        $headers    = [];
        foreach ($ci4headers as $key => $value) {
            $headers[$key] = $value->getValueLine();
        }
        if (count($setCookiesArray) !== 0) {
            $headers['Set-Cookie'] = $setCookiesArray;
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

    private function ci4Cookies(\CodeIgniter\HTTP\Response $ci4Response): array
    {
        $result = [];
        $cookies = $ci4Response->getCookies();
        foreach ($cookies as $cookie) {
            $headerString = $cookie->toHeaderString();
            $result[] = $headerString;
        }

        //Session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $sessionID        = session_id();
            $sessionName      = session_name();
            $cookiesSessionID = $this->_rRequest->getCookieParams()[$sessionName] ?? '';
            $cookiesParams    = session_get_cookie_params();
            /** @var \Config\Cookie */
            $cookieConfig           = config(Cookie::class);

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
                $result[] = $cookieStr;
            } elseif ($cookiesSessionID !== $sessionID) {
                $cookieStr = $this->getCookieString(
                    $sessionName,
                    '',
                    time(),
                    $cookieConfig->path,
                    $cookieConfig->domain,
                    $cookieConfig->secure,
                    $cookieConfig->httponly
                );
                $result[] = $cookieStr;
            }

            unset($_SESSION);
            session_write_close();
            session_id(null);
        }
        
        return $result;
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
