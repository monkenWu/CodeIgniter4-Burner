<?php

namespace Monken\CIBurner\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class InitLibrary extends BaseCommand
{
    protected $group       = 'burner';
    protected $name        = 'burner:init';
    protected $description = 'Initialize Burner required files.';
    protected $usage       = 'burner:init [use_driver]';
    protected $arguments   = [
        'use_driver' => 'You can choose Workerman or RoadRunner.',
    ];

    public function run(array $params)
    {
        $driver      = $params[0] ?? 'none';
        $allowDriver = ['RoadRunner', 'Workerman'];
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

        // init choose driver
        $this->{"init{$driver}"}();

        CLI::write(
            CLI::color("Burner initialization successful!\n", 'green') .
            sprintf(
                'Now you can use "%s" to run your CodeIgniter4 with %s Server!',
                CLI::color('burner:start', 'yellow'),
                $driver
            ),
        );
    }

    protected function initRoadRunner()
    {
        CLI::write(
            CLI::color("\nCopy configuration files ......\n", 'blue')
        );
        copy(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'Burner-RoadRunner.php',
            ROOTPATH . 'app/Config' . DIRECTORY_SEPARATOR . 'Burner.php'
        );
        $rr = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . '.rr.yaml');
        $rr = str_replace('{{static_paths}}', ROOTPATH . 'public', $rr);
        $rr = str_replace('{{reload_paths}}', realpath(APPPATH . '../'), $rr);
        file_put_contents(ROOTPATH . '.rr.yaml', $rr);

        CLI::write(
            CLI::color("Initializing RoadRunner Server binary ......\n", 'blue')
        );
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = '&&vendor\\bin\\rr get';
        } else {
            $command = ';./vendor/bin/rr get';
        }

        $init = popen('cd ' . ROOTPATH . $command, 'w');
        pclose($init);
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $targetPath = ROOTPATH . 'vendor\\bin\\rr_server.exe';
            $nowRRPath  = ROOTPATH . 'rr.exe';
        } else {
            $targetPath = ROOTPATH . 'vendor/bin/rr_server';
            $nowRRPath  = ROOTPATH . 'rr';
        }
        CLI::write(
            'Moveing RoadRunner Server binary to: ' .
            CLI::color("{$targetPath}", 'green') .
            "\n"
        );

        rename($nowRRPath, $targetPath);
        @chmod($targetPath, 0777 & ~umask());
    }

    protected function initWorkerman()
    {
        CLI::write(
            CLI::color("\nCopy configuration files ......\n", 'blue')
        );
        $configPath = ROOTPATH . 'app/Config' . DIRECTORY_SEPARATOR;
        copy(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'Burner-Workerman.php',
            $configPath . 'Burner.php'
        );
        $cnf = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'Workerman.php');
        $cnf = str_replace('{{static_path}}', ROOTPATH . 'public', $cnf);
        $cnf = str_replace('{{reload_path}}', realpath(APPPATH . '../'), $cnf);
        $cnf = str_replace('{{log_path}}', realpath(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'workerman.log'), $cnf);

        file_put_contents($configPath . 'Workerman.php', $cnf);
    }
}
