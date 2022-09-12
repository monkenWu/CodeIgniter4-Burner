<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Burner extends BaseConfig
{
    /**
     * You can choose Workerman or RoadRunner
     *
     * @var string
     */
    public $driver = 'Workerman';

    /**
     * Server config path.
     * Workerman driver default: ROOTPATH . 'app/Config'  . DIRECTORY_SEPARATOR . 'Workerman.php'
     * RoadRunner driver default: ROOTPATH . '.rr.yaml'
     *
     * @var string
     */
    public $serverConfigPath = ROOTPATH . 'Workerman.php';
}
