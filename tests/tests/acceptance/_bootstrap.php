<?php
use Symfony\Component\Process\Process;

// Here you can initialize variables that will be available to your tests

$command = 'exec ' . APP_ROOT . 'bin/phiremock --port 8086 --ip 0.0.0.0';
echo 'Running ' . $command . PHP_EOL;
$process = new Process($command);

register_shutdown_function(function () use ($process) {
    $process->stop(10, SIGTERM);
});

$process->start();
sleep(1);
