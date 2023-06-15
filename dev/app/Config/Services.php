<?php

namespace Config;

use CodeIgniter\Config\BaseService;

use Monken\CIBurner\Bridge\Override\Response;

class Services extends BaseService
{
    /**
     * The Response class models an HTTP response.
     *
     * @return ResponseInterface
     */
    public static function response(?App $config = null, bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('response', $config);
        }

        $config ??= config('App');

        return new Response($config);
    }

}
