<?php

namespace Monken\CIBurner;

interface IntegrationInterface
{
    public function initServer(string $configType = 'basic', string $frontLoader = '');

    public function startServer(string $frontLoader, bool $daemon = false, string $commands = '');

    public function stopServer(string $frontLoader, string $commands = '');

    public function restartServer(string $frontLoader, string $commands = '');

    public function reloadServer(string $frontLoader, string $commands = '');
}
