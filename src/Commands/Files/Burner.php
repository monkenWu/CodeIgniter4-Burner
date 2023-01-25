<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Burner extends BaseConfig
{
    /**
     * Default driver
     *
     * @var string 'RoadRunner', 'Workerman', 'OpenSwoole'
     */
    public $baseDriver = '{{base_driver}}';
}
