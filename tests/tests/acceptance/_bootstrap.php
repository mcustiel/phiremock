<?php
use Symfony\Component\Process\Process;

// Here you can initialize variables that will be available to your tests

$process = new Process('exec ' . APP_ROOT . 'bin/phiremock --port 8086 --ip 0.0.0.0');

register_shutdown_function(function () use ($process) {
    $process->stop(10, SIGTERM);
});

$process->start();
sleep(1);
