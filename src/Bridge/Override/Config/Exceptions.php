<?php

namespace Monken\CIBurner\Bridge\Override\Config;

use Config\Exceptions as BaseExceptionsConfig;
use Monken\CIBurner\Bridge\Debug\ExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use Throwable;

/**
 * Setup how the exception handler works.
 */
class Exceptions extends BaseExceptionsConfig
{
    public function handler(int $statusCode, Throwable $exception): ExceptionHandlerInterface
    {
        return new ExceptionHandler($this);
    }
}
