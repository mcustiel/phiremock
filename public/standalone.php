<?php
require __DIR__ . '/autoload.php';

use Mcustiel\SimpleRequest\RequestBuilder;
use Mcustiel\Phiremock\Server\Http\Implementation\ReactPhpServer;
use Mcustiel\Phiremock\Server\Phiremock;
use Mcustiel\Phiremock\Server\Model\Implementation\AutoStorage;
use Mcustiel\PowerRoute\PowerRoute;

if (PHP_SAPI != 'cli') {
    throw new \Exception('This is a standalone CLI application');
}

// require 'functions.php';

/*$stubs = new AutoStorage();
$cacheConfig = new \stdClass();
$cacheConfig->path = __DIR__ . '/../cache/requests/';
$cacheConfig->disabled = true;
$requestBuilder = new RequestBuilder($cacheConfig);

$powerRoute = new PowerRoute(
    require __DIR__ . '/../config/router-config.php',
    getActionFactory($requestBuilder, $stubs),
    getConditionsMatchersFactory()
);
*/
$application = new Phiremock(
    require __DIR__ . '/../config/router-config.php',
    new AutoStorage()
);

$server = new ReactPhpServer();
$server->setRequestHandler($application);
$server->listen(8086, '0.0.0.0');
