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

    public function run(array $params)
    {
        /** @var \Config\Burner */
        $burnerConfig = Factories::config('Burner');

        $driver      = $params['driver'] ?? $burnerConfig->driver;
        $allowDriver = ['RoadRunner', 'Workerman'];
        if (in_array($driver, $allowDriver, true) === false) {
            CLI::write(
                CLI::color(
                    sprintf(
                        'Error driver! We only support: %s , Now input driver: %s .',
                        implode(', ', $allowDriver),
                        $driver
                    ),
                    'red'
                )
            );

            return;
        }

        CLI::write(
            CLI::color("Starting CodeIgniter Burner. Use Driver: {$burnerConfig->driver} ...\n", 'blue')
        );

        "\\Monken\\CIBurner\\{$driver}\\EntryPoint"::run($burnerConfig);
    }
}
