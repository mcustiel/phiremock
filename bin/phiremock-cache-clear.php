<?php

use Mcustiel\Phiremock\Server\Config\Dependencies;

if (PHP_SAPI !== 'cli') {
    throw new \Exception('This is a standalone CLI application');
}

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $loader = require __DIR__ . '/../vendor/autoload.php';
} else {
    $loader = require __DIR__ . '/../../../autoload.php';
}

define('LOG_LEVEL', \Monolog\Logger::INFO);
define('APP_ROOT', dirname(__DIR__));

function deleteDirectoryRecursively($dir)
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
        if ($fileinfo->isDir()) {
            rmdir($fileinfo->getRealPath());
        } else {
            unlink($fileinfo->getRealPath());
        }
    }

    rmdir($dir);
}

$di = Dependencies::init();
$logger = $di->get('logger');
$cacheDirectory = sys_get_temp_dir() . '/phiremock/cache/requests/';
if (is_dir($cacheDirectory)) {
    $logger->info('Clearing phiremock cache...');
    deleteDirectoryRecursively($cacheDirectory);
    $logger->info('Cache deleted successfully.');
} else {
    $logger->info('No cache to delete.');
}
