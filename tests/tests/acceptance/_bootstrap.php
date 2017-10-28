<?php

use Symfony\Component\Process\Process;

// Here you can initialize variables that will be available to your tests

$expectationsDir = __DIR__ . '/../_data/expectations';
$command = 'exec php ' . APP_ROOT . 'bin/phiremock --port 8086 -e ' . $expectationsDir;
echo 'Running ' . $command . PHP_EOL;
$process = new Process($command);

register_shutdown_function(function () use ($process) {
    echo 'Terminating phiremock' . PHP_EOL;
    $process->stop(10, defined('SIGTERM') ? SIGTERM : null);
});

$process->start(function ($type, $buffer) {
    if (Process::ERR === $type) {
        echo 'ERR > ' . $buffer;
    } else {
        echo 'OUT > ' . $buffer;
    }
});
sleep(1);
