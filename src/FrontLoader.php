<?php

$vendorPath = '';
/**
 * Find autoload.php to locate the desired file location.
 */
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . '../../../autoload.php')) {
    // If you use Burner through 'Composer-Require'.
    $vendorPath = __DIR__ . DIRECTORY_SEPARATOR . '../../../';
} elseif (file_exists(__DIR__ . DIRECTORY_SEPARATOR . '../dev/vendor/autoload.php')) {
    // If you'r in Burner development mode.
    $vendorPath = __DIR__ . DIRECTORY_SEPARATOR . '../dev/vendor/';
}

if ($vendorPath === '') {
    echo 'Path configuration couldn\'t be loaed.' . PHP_EOL;

    exit;
}

require_once realpath($vendorPath . 'autoload.php');
$pathsConfig = realpath( APPPATH . '/Config/Paths.php');
// Path to the front loader (this file)
defined('FCPATH') || define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(FCPATH);

// Override is_cli()
if (! function_exists('is_cli')) {
    function is_cli(): bool
    {
        return false;
    }
}

// Ci4 4.2.0 init
require_once realpath($pathsConfig) ?: $pathsConfig;
$paths     = new \Config\Paths();
$botstorap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once realpath($botstorap);
require_once SYSTEMPATH . 'Config/DotEnv.php';
(new \CodeIgniter\Config\DotEnv(ROOTPATH))->load();
$app = \Config\Services::codeigniter();
$app->initialize();
$app->setContext('web');
