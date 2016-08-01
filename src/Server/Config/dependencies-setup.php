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
use Mcustiel\PowerRoute\Matchers\RegExp as RegExpMatcher;
use Mcustiel\PowerRoute\Common\Conditions\ConditionsMatcherFactory;
use Mcustiel\Phiremock\Server\Actions\VerifyRequestFound;
use Mcustiel\PowerRoute\InputSources\Body;
use Mcustiel\Phiremock\Server\Actions\ListExpectationsAction;
use Mcustiel\Phiremock\Server\Actions\ClearExpectationsAction;
use Mcustiel\Phiremock\Server\Actions\ClearScenariosAction;
use Mcustiel\Creature\SingletonLazyCreator;
use Mcustiel\Phiremock\Server\Actions\CountRequestsAction;
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
use Mcustiel\Phiremock\Server\Utils\ResponseStrategyFactory;
use Mcustiel\Phiremock\Server\Utils\Strategies\HttpResponseStrategy;
use Mcustiel\Phiremock\Server\Utils\Strategies\ProxyResponseStrategy;
use Mcustiel\Phiremock\Common\Http\RemoteConnectionInterface;
use Mcustiel\Phiremock\Common\Http\Implementation\GuzzleConnection;
use Symfony\Component\Cache\Adapter\FilesystemAdapter as Psr6CacheAdapter;
use Mcustiel\SimpleRequest\ParserGenerator;
use Mcustiel\SimpleRequest\Services\DoctrineAnnotationService;
use Mcustiel\SimpleRequest\Strategies\AnnotationParserFactory;
use Mcustiel\SimpleRequest\Services\PhpReflectionService;
use Mcustiel\Phiremock\Server\Utils\Strategies\RegexResponseStrategy;

$di = new DependencyInjectionService();

$di->register('logger', function () {
    // create a log channel
    $log = new Logger('stdoutLogger');
    $log->pushHandler(new StreamHandler(STDOUT, LOG_LEVEL));

    return $log;
});

$di->register(RemoteConnectionInterface::class, function () {
    return new GuzzleConnection(new GuzzleHttp\Client());
});

$di->register(HttpResponseStrategy::class, function () use ($di) {
    return new HttpResponseStrategy($di->get('logger'));
});

$di->register(RegexResponseStrategy::class, function () use ($di) {
    return new RegexResponseStrategy($di->get('logger'));
});

$di->register(ProxyResponseStrategy::class, function () use ($di) {
    return new ProxyResponseStrategy(
        $di->get(RemoteConnectionInterface::class),
        $di->get('logger')
    );
});

$di->register('responseStrategyFactory', function () use ($di) {
    return new ResponseStrategyFactory($di);
});

$di->register('config', function () {
    return RouterConfig::get();
});

$di->register('homePathService', function () {
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

$di->register('requestBuilder', function () {
    $cache = new Psr6CacheAdapter(
        'phiremock',
        3600,
        sys_get_temp_dir() . '/phiremock/cache/requests/'
    );

    return new RequestBuilder(
        $cache,
        new ParserGenerator(
            new DoctrineAnnotationService(),
            new AnnotationParserFactory(),
            new PhpReflectionService()
        )
    );
});

$di->register('fileExpectationsLoader', function () use ($di) {
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
        'url'    => new SingletonLazyCreator(Url::class),
        'header' => new SingletonLazyCreator(Header::class),
        'body'   => new SingletonLazyCreator(Body::class),
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
        'isEqualTo'    => new SingletonLazyCreator(Equals::class),
        'matches'      => new SingletonLazyCreator(RegExpMatcher::class),
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
                $di->get('logger'),
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
        'serverError'    => new SingletonLazyCreator(ServerError::class),
        'clearScenarios' => new SingletonLazyCreator(
            ClearScenariosAction::class,
            [$di->get('scenarioStorage')]
        ),
        'checkExpectations' => new SingletonLazyCreator(
            SearchRequestAction::class,
            [
                $di->get('expectationStorage'),
                $di->get('requestExpectationComparator'),
                $di->get('logger'),
            ]
        ),
        'verifyExpectations' => new SingletonLazyCreator(
            VerifyRequestFound::class,
            [
                $di->get('scenarioStorage'),
                $di->get('logger'),
                $di->get('responseStrategyFactory'),
            ]
        ),
        'countRequests' => new SingletonLazyCreator(
            CountRequestsAction::class,
            [
                $di->get('requestBuilder'),
                $di->get('requestStorage'),
                $di->get('requestExpectationComparator'),
                $di->get('logger'),
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
