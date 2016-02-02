<?php
namespace Mcustiel\Phiremock\Server;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Mcustiel\PowerRoute\PowerRoute;
use Mcustiel\Phiremock\Server\Http\RequestHandlerInterface;
use Mcustiel\Phiremock\Server\Model\Implementation\AutoStorage;
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
use Mcustiel\PowerRoute\Matchers\RegExp;
use Mcustiel\PowerRoute\Common\Conditions\ConditionsMatcherFactory;

class Phiremock implements RequestHandlerInterface
{
    private $storage;

    private $router;

    private $actionFactory;

    private $inputSourceFactory;

    private $matcherFactory;

    public function __construct(array $config) {
        $this->storage = new AutoStorage();
        $this->router = $this->createRouter($config);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Mcustiel\Phiremock\Server\Http\RequestHandler::execute()
     */
    public function execute(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->router->start($request, $response);
    }

    private function createRouter($config)
    {
        return new PowerRoute(
            $config,
            $this->getActionFactory(),
            $this->getConditionsMatchersFactory()
        );
    }

    private function getActionFactory()
    {
        if ($this->actionFactory === null) {
            $this->actionFactory = new ActionFactory([
                'addExpectation' => $this->getAddExpectationAction(),
                //'listExpectations' => new ListExpectationAction($storage),
                'serverError' => [ServerError::class],
                'parseExpectations' => $this->getSearchExpectationAction()
            ]);
        }
        return $this->actionFactory;
    }

    private function getSearchExpectationAction()
    {
        return new SearchRequestAction($this->storage, $this->getComparator());
    }

    private function getAddExpectationAction()
    {
        return new AddExpectationAction($this->getRequestBuilder(), $this->storage);
    }

    private function getRequestBuilder()
    {
        $cacheConfig = new \stdClass();
        $cacheConfig->path = __DIR__ . '/../../cache/requests/';
        $cacheConfig->disabled = true;
        return new RequestBuilder($cacheConfig);
    }

    private function getComparator()
    {
        return new RequestExpectationComparator($this->getMatcherFactory(), $this->getInputSourceFactory());
    }

    function getInputSourceFactory()
    {
        if ($this->inputSourceFactory === null) {
            $this->inputSourceFactory = new InputSourceFactory([
                'method' => [Method::class],
                'url' => [Url::class],
                'header' => [Header::class],
            ]);
        }
        return $this->inputSourceFactory;
    }

    function getMatcherFactory()
    {
        if ($this->matcherFactory === null) {
            $this->matcherFactory = new MatcherFactory([
                'isEqualTo' => [Equals::class],
                'matchesPattern' => [RegExp::class],
            ]);
        }
        return $this->matcherFactory;
    }

    function getConditionsMatchersFactory()
    {
        return new ConditionsMatcherFactory(
            $this->getInputSourceFactory(),
            $this->getMatcherFactory()
        );
    }
}
