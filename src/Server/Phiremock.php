<?php
namespace Mcustiel\Phiremock\Server;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Mcustiel\PowerRoute\PowerRoute;
use Mcustiel\Phiremock\Server\Http\RequestHandlerInterface;
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

class Phiremock implements RequestHandlerInterface
{
    private $expectationStorage;

    private $scenarioStorage;

    private $router;

    private $actionFactory;

    private $inputSourceFactory;

    private $matcherFactory;

    public function __construct(
        array $config,
        ExpectationStorage $expectationStorage,
        ScenarioStorage $scenarioStorage
    ) {
        $this->expectationStorage = $expectationStorage;
        $this->scenarioStorage = $scenarioStorage;
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
                'listExpectations' => new ListExpectationsAction($this->expectationStorage),
                'clearExpectations' => new ClearExpectationsAction($this->expectationStorage),
                'serverError' => [ServerError::class],
                'clearScenarios' => new ClearScenariosAction($this->scenarioStorage),
                'checkExpectations' => $this->getSearchExpectationAction(),
                'verifyExpectations' => new VerifyRequestFound($this->scenarioStorage),
            ]);
        }
        return $this->actionFactory;
    }

    private function getSearchExpectationAction()
    {
        return new SearchRequestAction($this->expectationStorage, $this->getComparator());
    }

    private function getAddExpectationAction()
    {
        return new AddExpectationAction(
            $this->getRequestBuilder(),
            $this->expectationStorage
        );
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
        return new RequestExpectationComparator(
            $this->getMatcherFactory(),
            $this->getInputSourceFactory(),
            $this->scenarioStorage
        );
    }

    private function getInputSourceFactory()
    {
        if ($this->inputSourceFactory === null) {
            $this->inputSourceFactory = new InputSourceFactory([
                'method' => [Method::class],
                'url' => [Url::class],
                'header' => [Header::class],
                'body' => [Body::class]
            ]);
        }
        return $this->inputSourceFactory;
    }

    private function getMatcherFactory()
    {
        if ($this->matcherFactory === null) {
            $this->matcherFactory = new MatcherFactory([
                'isEqualTo' => [Equals::class],
                'matchesPattern' => [RegExp::class],
                'isSameString' => [CaseInsensitiveEquals::class],
            ]);
        }
        return $this->matcherFactory;
    }

    private function getConditionsMatchersFactory()
    {
        return new ConditionsMatcherFactory(
            $this->getInputSourceFactory(),
            $this->getMatcherFactory()
        );
    }
}
