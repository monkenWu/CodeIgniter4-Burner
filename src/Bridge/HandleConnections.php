<?php

namespace Monken\CIBurner\Bridge;

use Closure;
use CodeIgniter\Cache\Handlers\RedisHandler as CacheRedisHandler;
use CodeIgniter\Config\Services;
use Config\Burner as BurnerConfig;
use Config\Database;
use RedisException;
use Throwable;

/**
 * Handle Connections
 *
 * Manage external servers that require reconnection
 * or automatic disconnection due to memory persistence.
 */
class HandleConnections
{
    public static function reconnect(BurnerConfig $config)
    {
        if ($config->dbAutoClose) {
            self::closeDBConnect();
        } else {
            self::reconnectDB();
        }

        if ($config->cacheAutoClose === false) {
            self::reconnectCache();
        }
    }

    public static function reconnectCache()
    {
        $cacheInstance = Services::cache();
        if ($cacheInstance instanceof CacheRedisHandler) {
            $reConnector = Closure::bind(function () {
                try {
                    if (! $this->redis->ping()) {
                        try {
                            $this->initialize();
                        } catch (\CodeIgniter\Exceptions\CriticalError $e) {
                            Services::resetSingle('cache');
                        }
                    }
                } catch (RedisException $e) {
                    try {
                        $this->initialize();
                    } catch (\CodeIgniter\Exceptions\CriticalError $e) {
                        Services::resetSingle('cache');
                    }
                }
            }, $cacheInstance, CacheRedisHandler::class);
            $reConnector();
        }
    }

    public static function closeDBConnect()
    {
        $dbInstances = Database::getConnections();

        foreach ($dbInstances as $connection) {
            $connection->close();
        }
    }

    public static function reconnectDB()
    {
        $dbInstances = Database::getConnections();

        foreach ($dbInstances as $connection) {
            if ($connection->DBDriver === 'MySQLi') {
                try {
                    $connection->mysqli->ping();
                } catch (Throwable $th) {
                    $connection->reconnect();
                }
            } else {
                $connection->reconnect();
            }
        }
    }
}
