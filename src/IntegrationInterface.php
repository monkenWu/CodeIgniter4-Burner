<?php

namespace Monken\CIBurner;

Interface IntegrationInterface
{
    public function initServer();
    public function startServer(string $frontLoader);
}