<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

class Workerman extends BaseConfig
{
    /**
     * Public static files location path.
     *
     * @var string
     */
    public $staticDir = '{{static_path}}';

    /**
     * Public access to files with these filename-extension is prohibited.
     *
     * @var array
     */
    public $staticForbid = ['htaccess', 'php'];

    /**
     * Auto-scan changed files
     *
     * @var bool
     */
    public $autoReload = false;

    /**
     * Auto-scan of the root directory
     *
     * @var string
     */
    public $autoReloadDir = '{{reload_path}}';

    /**
     * Files with these filename-extension will be auto-scanned.
     *
     * @var array
     */
    public $autoReloadScanExtensions = ['php', 'env'];

    /**
     * Set how many processes to start for the current Worker instance.
     * In non-development environment, the recommended number of workers
     * is twice the number of cpu cores.
     *
     * @var int
     */
    public $workerCount = 1;

    /**
     * Set which user to run the Worker instance as,
     * Windows does not support this setting.
     *
     * @var string
     */
    public $workerUser = 'www-data';

    /**
     * dump() output to the terminal will be redirected to
     * the specified file when workerman run as daemon mode.
     *
     * @var string
     */
    public $stdoutFile = '/dev/null';

    /**
     * Record information about the workerman framework itself,
     * such as start, stop, and some fatal errors(if any).
     *
     * @var string
     */
    public $logFile = '{{log_path}}';

    /**
     * TCP HTTP service listening port
     *
     * @var int
     */
    public $listeningPort = 8080;

    /**
     * Whether to enable ssl connection
     *
     * @var bool
     */
    public $ssl = false;

    /**
     * ssl cert or pem file path
     *
     * @var string
     */
    public $sslCertFilePath = 'server.pem';

    /**
     * ssl key file path
     *
     * @var string
     */
    public $sslKeyFilePath = 'server.key';

    /**
     * ssl verify peer
     *
     * @var bool
     */
    public $sslVerifyPeer = false;

    /**
     * If it's a self-signed certificate, you need to turn on this option
     *
     * @var bool
     */
    public $sslAllowSelfSigned = false;

    /**
     * Set the default application layer send buffer size for all connections
     *
     * @var int byte
     */
    public $defaultMaxSendBufferSize = 1048576;

    /**
     * Set the max package size can be received
     *
     * @var int byte
     */
    public $defaultMaxPackageSize = 10485760;

    /**
     * You can declare some additional worker setting in this method.
     *
     * @return void
     */
    public function initWorker(Worker &$worker)
    {
    }

    /**
     * Each tcp connection will automatically run this method before
     * starting the CodeIgniter4 processing cycle, and you can control
     * the settings for each connection through this method.
     *
     * @return void
     */
    public function runtimeTcpConnection(TcpConnection &$tcpConnection)
    {
    }
}
