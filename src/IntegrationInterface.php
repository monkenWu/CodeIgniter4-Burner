<?php

namespace Monken\CIBurner;

Interface IntegrationInterface
{
    public function initServer(string $configType = 'basic', string $frontLoader = '');
    public function startServer(string $frontLoader, string $commands = '');
}