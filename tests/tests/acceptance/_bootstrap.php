<?php
// Here you can initialize variables that will be available to your tests

/*
touch(__DIR__ . '/../_output/proc-output.txt');
touch(__DIR__ . '/../_output/proc-error.txt');

$descriptorSpec = [
    ['pipe', 'r'],
    ['file', __DIR__ . '/../_output/proc-output.txt', 'w'],
    ['file', __DIR__ . '/../_output/proc-error.txt', 'a']
];

$env = [];
$pipes = [];

$process = proc_open(
    'php ' . APP_ROOT . 'public/standalone.php --port 8086 --ip 0.0.0.0',
    $descriptorSpec,
    $pipes,
    null,
    null,
    ['bypass_shell' => true]
);
echo "Running";
if (!is_resource($process)) {
    throw new \Exception('Can not run phiremock');
}
var_export($process);
register_shutdown_function(function () use ($process, $pipes) {
    echo "Shutting down\n";
    foreach ($pipes as $pipe) {
        echo "Checking pipe\n";
        if (is_resource($pipe)) {
            echo "Closing pipe\n";
            fflush($pipe);
            fclose($pipe);
        }
    }
    echo "Closing process\n";
    echo proc_terminate($process);
    echo PHP_EOL;
    echo proc_close($process);
});
*/
