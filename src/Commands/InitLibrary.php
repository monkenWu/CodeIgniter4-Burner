<?php

namespace Monken\CIBurner\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class InitLibrary extends BaseCommand
{
    protected $group       = 'burner';
    protected $name        = 'burner:init';
    protected $description = 'Initialize Burner required files.';
    protected $usage       = 'burner:init <use_driver> <config_type>';
    protected $arguments   = [
        'use_driver'  => 'You can choose Workerman, RoadRunner and OpenSwoole.',
        'config_type' => 'You can choose basic or any other config type provided by the driver.',
    ];

    public function run(array $params)
    {
        $driver      = $params[0] ?? 'none';
        $configType  = $params[1] ?? 'basic';
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
                        ucwords($driver)
                    ),
                    'white',
                    'blue'
                )
            );
            CLI::write();

            return;
        }

        // init choose driver
        /**
         * @var \Monken\CIBurner\IntegrationInterface
         */
        $integration = new $driverIntegrationClassName();
        $loaderPath  = realpath(__DIR__ . '/../FrontLoader.php');
        $integration->initServer($configType, $loaderPath);
        $this->publishBurnerConfig($driver);

        CLI::write(
            CLI::color("Burner initialization successful!\n", 'green') .
            'Now you can run burner with the follwing command:',
        );
        CLI::write();
        CLI::write(
            CLI::color(
                'burner:start',
                'white',
                'blue'
            )
        );
        CLI::write();
    }

    protected function publishBurnerConfig(string $driver)
    {
        $basePath   = ROOTPATH . 'app/Config' . DIRECTORY_SEPARATOR;
        $configPath = $basePath . 'Burner.php';

        if (file_exists($configPath)) {
            rename($configPath, $basePath . 'Burner.backup.' . time() . '.php');
        }

        $cnf = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'Burner.php');
        $cnf = str_replace('{{base_driver}}', $driver, $cnf);
        file_put_contents($configPath, $cnf);
    }
}
