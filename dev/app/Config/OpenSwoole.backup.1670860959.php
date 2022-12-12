<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use Monken\CIBurner\OpenSwoole\Worker;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

class OpenSwoole extends BaseConfig
{
    /**
     * Swoole Http Driver.
     * You can use Swoole\HTTP\Server or Swoole\WebSocket\Server .
     *
     * @var string
     */
    public $httpDriver = Server::class;

    /**
     * TCP HTTP service listening ip
     *
     * @var string
     */
    public $listeningIp = '0.0.0.0';

    /**
     * TCP HTTP service listening port
     *
     * @var int
     */
    public $listeningPort = 8080;

    /**
     * Which mode to start the server in SWOOLE_PROCESS or SWOOLE_BASE
     *
     * @var int
     *
     * @see https://openswoole.com/docs/modules/swoole-server-construct
     */
    public $mode = SWOOLE_BASE;

    /**
     * The socket type of the server.
     *
     * @var int
     *
     * @see https://openswoole.com/docs/modules/swoole-server-construct
     */
    public $type = SWOOLE_SOCK_TCP;

    /**
     * Swoole Setting Configuration Options
     *
     * @var array
     *
     * @see https://openswoole.com/docs/modules/swoole-http-server/configuration
     * @see https://openswoole.com/docs/modules/swoole-server/configuration
     */
    public $config = [
        'worker_num'            => 1,
        'max_request'           => 0,
        'document_root'         => '/app/CodeIgniter4-Burner/dev/public',
        'enable_static_handler' => true,
        'log_level'             => 0,
        'log_file'              => '/app/CodeIgniter4-Burner/dev/writable/logs/OpenSwoole.log',
    ];

    /**
     * You can declare some additional server setting in this method.
     *
     * @return void
     */
    public function server(Server $server)
    {
        $config = $this;
        $server->on('Start', static function (Server $server) use ($config) {
            fwrite(STDOUT, sprintf(
                'Swoole %s server is started at %s:%d %s',
                explode('\\', $config->httpDriver)[1],
                $config->listeningIp,
                $config->listeningPort,
                PHP_EOL
            ));
        });

        $server->on('request', static function (Request $swooleRequest, Response $swooleResponse) {
            // Burner handles CodeIgniter4 entry points.
            Worker::httpProcesser($swooleRequest, $swooleResponse);
        });
    }
}
