<?php
use Mcustiel\DependencyInjection\DependencyInjectionService;
use Mcustiel\PowerRoute\PowerRoute;
use Mcustiel\PowerRoute\Common\Factories\ActionFactory;
use Mcustiel\SimpleRequest\RequestBuilder;
use Mcustiel\PowerRoute\Actions\ServerError;
use Mcustiel\Phiremock\Server\Actions\AddExpectationAction;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;
use Mcustiel\Phiremock\Server\Actions\SearchRequestAction;
use Mcustiel\PowerRoute\Common\Factories\InputSourceFactory;
use Mcustiel\PowerRoute\InputSources\Method;
use Mcustiel\PowerRoute\InputSources\Url;
use Mcustiel\PowerRoute\InputSources\Header;
use Mcustiel\PowerRoute\Common\Factories\MatcherFactory;
use Mcustiel\PowerRoute\Matchers\Equals;
use Mcustiel\PowerRoute\Matchers\CaseInsensitiveEquals;
use Mcustiel\PowerRoute\Matchers\RegExp;
use Mcustiel\PowerRoute\Common\Conditions\ConditionsMatcherFactory;
use Mcustiel\Phiremock\Server\Actions\VerifyRequestFound;
use Mcustiel\PowerRoute\InputSources\Body;
use Mcustiel\Phiremock\Server\Actions\ListExpectationsAction;
use Mcustiel\Phiremock\Server\Actions\ClearExpectationsAction;
use Mcustiel\Phiremock\Server\Actions\ClearScenariosAction;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;
use Mcustiel\Creature\SingletonLazyCreator;
use Mcustiel\Phiremock\Server\Actions\CountRequestsAction;
use Mcustiel\Phiremock\Server\Model\RequestStorage;
use Mcustiel\Phiremock\Server\Model\Implementation\ScenarioAutoStorage;
use Mcustiel\Phiremock\Server\Model\Implementation\ExpectationAutoStorage;
use Mcustiel\Phiremock\Server\Model\Implementation\RequestAutoStorage;
use Mcustiel\Phiremock\Server\Phiremock;
use Mcustiel\Phiremock\Server\Config\RouterConfig;
use Mcustiel\Phiremock\Server\Http\Implementation\ReactPhpServer;
use Mcustiel\Phiremock\Server\Actions\StoreRequestAction;
use Mcustiel\Phiremock\Server\Actions\ResetRequestsCountAction;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Mcustiel\Phiremock\Server\Utils\HomePathService;
use Mcustiel\Phiremock\Server\Utils\FileExpectationsLoader;

$di = new DependencyInjectionService();

$di->register('logger', function () {
    // create a log channel
    $log = new Logger('stdoutLogger');
    $log->pushHandler(new StreamHandler(STDOUT, LOG_LEVEL));

    return $log;
});

$di->register('config', function () {
    return RouterConfig::get();
});

$di->register('homePathService', function() {
    return new HomePathService();
});

$di->register('server', function () use ($di) {
    $server = new ReactPhpServer($di->get('logger'));
    return $server;
});

$di->register('application', function () use ($di) {
    return new Phiremock($di->get('router'), $di->get('logger'));
});

$di->register('expectationStorage', function () {
    return new ExpectationAutoStorage();
});

$di->register('requestStorage', function () {
    return new RequestAutoStorage();
});

$di->register('scenarioStorage', function () {
    return new ScenarioAutoStorage();
});

$di->register('requestExpectationComparator', function () use ($di) {
    return new RequestExpectationComparator(
        $di->get('matcherFactory'),
        $di->get('inputSourceFactory'),
        $di->get('scenarioStorage'),
        $di->get('logger')
    );
});

$di->register('requestBuilder', function () use ($di) {
    $cachePath = sys_get_temp_dir() . '/phiremock/cache/requests/';
    if (!is_dir($cachePath)) {
        mkdir($cachePath, 0777, true);
    }

    $cacheConfig = new \stdClass();
    $cacheConfig->path = $cachePath;
    $cacheConfig->disabled = false;

    return new RequestBuilder($cacheConfig);
});

$di->register('fileExpectationsLoader', function() use ($di) {
    return new FileExpectationsLoader(
        $di->get('requestBuilder'),
        $di->get('expectationStorage'),
        $di->get('logger')
    );
});

$di->register('conditionsMatcherFactory', function () use ($di) {
    return new ConditionsMatcherFactory(
        $di->get('inputSourceFactory'),
        $di->get('matcherFactory')
    );
});

$di->register('inputSourceFactory', function () {
    return new InputSourceFactory([
        'method' => new SingletonLazyCreator(Method::class),
        'url' => new SingletonLazyCreator(Url::class),
        'header' => new SingletonLazyCreator(Header::class),
        'body' => new SingletonLazyCreator(Body::class)
    ]);
});

$di->register('router', function () use ($di) {
    return new PowerRoute(
        $di->get('config'),
        $di->get('actionFactory'),
        $di->get('conditionsMatcherFactory')
    );
});

$di->register('matcherFactory', function () {
    return new MatcherFactory([
        'isEqualTo' => new SingletonLazyCreator(Equals::class),
        'matches' => new SingletonLazyCreator(RegExp::class),
        'isSameString' => new SingletonLazyCreator(CaseInsensitiveEquals::class),
    ]);
});

$di->register('actionFactory', function () use ($di) {
    return new ActionFactory([
        'addExpectation' => new SingletonLazyCreator(
            AddExpectationAction::class,
            [
                $di->get('requestBuilder'),
                $di->get('expectationStorage'),
                $di->get('logger')
            ]
        ),
        'listExpectations' => new SingletonLazyCreator(
            ListExpectationsAction::class,
            [$di->get('expectationStorage')]
        ),
        'clearExpectations' => new SingletonLazyCreator(
            ClearExpectationsAction::class,
            [$di->get('expectationStorage')]
        ),
        'serverError' => new SingletonLazyCreator(ServerError::class),
        'clearScenarios' => new SingletonLazyCreator(
            ClearScenariosAction::class,
            [$di->get('scenarioStorage')]
        ),
        'checkExpectations' => new SingletonLazyCreator(
            SearchRequestAction::class,
            [
                $di->get('expectationStorage'),
                $di->get('requestExpectationComparator'),
                $di->get('logger')
            ]
        ),
        'verifyExpectations' => new SingletonLazyCreator(
            VerifyRequestFound::class,
            [$di->get('scenarioStorage'), $di->get('logger')]
        ),
        'countRequests' => new SingletonLazyCreator(
            CountRequestsAction::class,
            [
                $di->get('requestBuilder'),
                $di->get('requestStorage'),
                $di->get('requestExpectationComparator'),
                $di->get('logger')
            ]
        ),
        'resetCount' => new SingletonLazyCreator(
            ResetRequestsCountAction::class,
            [$di->get('requestStorage')]
        ),
        'storeRequest' => new SingletonLazyCreator(
            StoreRequestAction::class,
            [$di->get('requestStorage')]
        ),
    ]);
});

return $di;
