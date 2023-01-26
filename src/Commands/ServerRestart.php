<?php

namespace Monken\CIBurner\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;

class ServerRestart extends BaseCommand
{
    use GeneratorTrait;

    protected $group       = 'burner';
    protected $name        = 'burner:restart';
    protected $description = 'Restart CodeIgniter Burner server.';
    protected $usage       = 'burner:restart [options]';

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--driver' => 'You can choose Workerman, RoadRunner and OpenSwoole.',
    ];

    public function run(array $params)
    {
        /** @var \Config\Burner */
        $burnerConfig = Config('Burner');
        if (null === $burnerConfig) {
            CLI::write(
                CLI::color(
                    'The "app/Burner.php" was not found. You must first run the "burner:init [driver]" command to initialize this library.',
                    'red'
                )
            );

            return;
        }

        $driver      = CLI::getOption('driver') ?? $burnerConfig->baseDriver;
        $allowDriver = ['RoadRunner', 'Workerman', 'OpenSwoole'];
        if (in_array($driver, $allowDriver, true) === false) {
            CLI::write(
                CLI::color(
                    sprintf(
                        'Error driver! We only support: %s. Your current driver is: "%s".',
                        implode(', ', $allowDriver),
                        $driver
                    ),
                    'red'
                )
            );

            return;
        }

        $driverIntegrationClassName = sprintf('Monken\CIBurner\%s\Integration', $driver);
        if (class_exists($driverIntegrationClassName) === false) {
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
            CLI::color("CodeIgniter Burner is restarting. Use Driver: {$driver} ...\n", 'blue')
        );

        // init choose driver
        /**
         * @var \Monken\CIBurner\IntegrationInterface
         */
        $integration = new $driverIntegrationClassName();
        if ($driver === 'RoadRunner') {
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
        } else {
            $loaderPath = realpath(__DIR__ . '/../FrontLoader.php');
        }
        $integration->restartServer($loaderPath);
    }
}
