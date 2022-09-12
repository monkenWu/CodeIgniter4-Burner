<?php

namespace Monken\CIBurner\Workerman;

use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;

class StaticFile
{
    protected static $public = ROOTPATH . 'public' . DIRECTORY_SEPARATOR;
    protected static $forbid = ['htaccess', 'php'];

    /**
     * If Request With Static File
     *
     * @param string $path
     */
    public static function withFile(Request $request): ?Response
    {
        $filePath = realpath(static::$public . parse_url($request->path(), PHP_URL_PATH));
        if ($filePath === false) {
            return null;
        }

        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (in_array($ext, static::$forbid, true)) {
            return null;
        }

        $response = (new Response())->withFile($filePath);
        if ($response->getStatusCode() === 200) {
            clearstatcache();

            return $response;
        }

        return null;
    }

    public static function forbid(array $list = ['htaccess', 'php'])
    {
        self::$forbid = $list;
    }

    public static function publicPath(string $path = ROOTPATH . 'public' . DIRECTORY_SEPARATOR)
    {
        self::$public = $path;
    }
}
