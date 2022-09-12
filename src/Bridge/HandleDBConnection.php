<?php

namespace Monken\CIBurner\Bridge;

use Config\Database;
use Throwable;

class HandleDBConnection
{
    public static function closeConnect()
    {
        $dbInstances = Database::getConnections();

        foreach ($dbInstances as $connection) {
            $connection->close();
        }
    }

    public static function reconnect()
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
