<?php

namespace Monken\CIBurner\Bridge;

use CodeIgniter\Config\Services;
use Psr\Http\Message\UriInterface;

class UriBridge
{
    private static $_rURI;

    public static function setUri(UriInterface $rURI)
    {
        Services::uri(null, false);

        self::$_rURI = $rURI;
        self::transferPath();
        self::transferQuery();
    }

    protected static function transferPath()
    {
        $rPath = self::$_rURI->getPath();

        if ($rPath === '/') {
            Services::request()->setPath($rPath);

            return;
        }

        $pathArr = explode('/', $rPath);

        if ($pathArr[1] === 'index.php') {
            unset($pathArr[1]);
            array_values($pathArr);
        }
        if ($pathArr[count($pathArr) - 1] === '') {
            unset($pathArr[count($pathArr) - 1]);
            array_values($pathArr);
        }

        $path = '/' . implode('/', $pathArr);
        Services::request()->setPath($path);
    }

    protected static function transferQuery()
    {
        Services::uri()->setQuery(self::$_rURI->getQuery());
    }
}
