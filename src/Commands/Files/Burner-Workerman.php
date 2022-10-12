<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Burner extends BaseConfig
{
    /**
     * You can choose Workerman, RoadRunner, OpenSwoole
     *
     * @var string
     */
    public $driver = 'Workerman';

    /**
     * Server config path.
     * Workerman driver default: ROOTPATH . 'app/Config'  . DIRECTORY_SEPARATOR . 'Workerman.php'
     * RoadRunner driver default: ROOTPATH . '.rr.yaml'
     * OpenSwoole driver default: ROOTPATH . 'app/Config'  . DIRECTORY_SEPARATOR . 'OpenSwoole.php
     *
     * @var string
     */
    public $serverConfigPath = ROOTPATH . 'app/Config' . DIRECTORY_SEPARATOR . 'Workerman.php';
}
