<?php
require __DIR__ . '/autoload.php';

use Mcustiel\Phiremock\Server\Http\Implementation\ReactPhpServer;
use Mcustiel\Phiremock\Server\Phiremock;
use Mcustiel\Phiremock\Server\Model\Implementation\ScenarioAutoStorage;
use Mcustiel\Phiremock\Server\Model\Implementation\ExpectationAutoStorage;

if (PHP_SAPI != 'cli') {
    throw new \Exception('This is a standalone CLI application');
}

$options = CommandLine::parseArgs($argv);

$port = isset($options['port']) ? $options['port'] : (isset($options['p']) ? $options['p'] : '8086');
$interface = isset($options['ip']) ? $options['ip'] : (isset($options['i']) ? $options['i'] : '0.0.0.0');

$scenarioStorage = new ScenarioAutoStorage();
$expectationStorage = new ExpectationAutoStorage();

$application = new Phiremock(
    require __DIR__ . '/../config/router-config.php',
    $expectationStorage,
    $scenarioStorage
);

$server = new ReactPhpServer();
$server->setRequestHandler($application);

register_shutdown_function(function () use ($server) {
    $server->shutdown();
});

$server->listen($port, $interface);
