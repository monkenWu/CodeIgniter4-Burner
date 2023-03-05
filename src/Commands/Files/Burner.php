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

    /**
     * Does the DB connection close automatically at the end of each request?
     *
     * @var bool
     */
    public $dbAutoClose = false;

    /**
     * Does the Cache connection (e.g. Redis) automatically closed
     * at the end of each request?
     *
     * @var bool
     */
    public $cacheAutoClose = false;

    /**
     * Burner automatically initializes all Instances in the Services
     * after an HTTP response, in case an already-used singleton affects
     * the next request.
     * If your Service does not need to be initialized, then declare
     * the Service name in this string array, which will make the Service
     * persistent and reusable in the Worker.
     *
     * @var string[]
     */
    public $skipInitServices = [];
}
