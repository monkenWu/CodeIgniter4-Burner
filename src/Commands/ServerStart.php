<?php

namespace Monken\CIBurner\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\Factories;

class ServerStart extends BaseCommand
{
    protected $group       = 'burner';
    protected $name        = 'burner:start';
    protected $description = 'Start CodeIgniter Burner.';
    protected $usage       = 'burner:init [use_driver]';
    protected $arguments   = [
        'use_driver' => 'You can choose Workerman, RoadRunner and OpenSwoole.',
    ];

    public function run(array $params)
    {
        $driver      = $params[0] ?? 'none';
        $allowDriver = ['RoadRunner', 'Workerman', 'OpenSwoole'];
        if (in_array($driver, $allowDriver, true) === false) {
            CLI::write(
                CLI::color(
                    sprintf(
                        'Error driver! We only support: %s. Your current driver is: %s .',
                        implode(', ', $allowDriver),
                        $driver
                    ),
                    'red'
                )
            );
            return;
        }

        $driverIntegrationClassName = sprintf('Monken\CIBurner\%s\Integration', $driver);
        if(class_exists($driverIntegrationClassName) == false) {
            CLI::write(
                CLI::color(
                    sprintf(
                        'The "%s" Driver is not yet installed, you can install it with the following command:',
                        $driver
                    ),
                    'red'
                )
            );
            CLI::write();
            CLI::write(
                CLI::color(
                    sprintf(
                        'composer require monken/codeigniter4-burner-%s',
                        strtolower($driver)
                    ),
                    'white',
                    'blue'
                )
            );
            CLI::write();
            return;
        }

        CLI::write(
            CLI::color("Starting CodeIgniter Burner. Use Driver: {$driver} ...\n", 'blue')
        );

        // init choose driver
        /**
         * @var \Monken\CIBurner\IntegrationInterface
         */
        $integration = new $driverIntegrationClassName();
        if($driver == 'RoadRunner'){
            $workDir = __DIR__ . DIRECTORY_SEPARATOR;
            if (file_exists($workDir . '../../../../autoload.php')) {
                $loaderPath = realpath($workDir . '../../../../bin/rr_server');
            } elseif (file_exists('../../dev/vendor/autoload.php')) {
                $loaderPath = realpath('../../dev/vendor/bin/rr_server');
            }
            if ($loaderPath === false) {
                CLI::write(
                    CLI::color(
                        "Error! Roadrunner Server is not init. Please use 'burner:init RoadRunner' to init Roadrunner.",
                        'red'
                    )
                );
                return;
            }
        }else{
            $loaderPath = realpath(__DIR__ . '/../FrontLoader.php');
        }
        $argv = $_SERVER['argv'];
        $commands = '';
        if(count($argv) > 3){
            $commands = implode(' ', array_slice($argv, 3));
        }
        $integration->startServer($loaderPath, $commands);
    }
}
