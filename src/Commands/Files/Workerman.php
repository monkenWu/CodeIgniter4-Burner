<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Workerman extends BaseConfig
{
    public $staticDir = '{{static_paths}}';
    public $reloadDir = '{{reload_paths}}';
}
