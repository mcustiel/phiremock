<?php
require __DIR__ . '/vendor/autoload.php';

use Mcustiel\Phiremock\Server\Domain\Expectation;
use Mcustiel\SimpleRequest\RequestBuilder;
use Zend\Diactoros\Response\HtmlResponse;
use React\Http\Request;
use React\Http\Response;
use React\Stream\BufferedSink;
use Mcustiel\PowerRoute\PowerRoute;
use Mcustiel\PowerRoute\Common\ActionFactory;
use Mcustiel\PowerRoute\Common\MatcherFactory;
use Mcustiel\PowerRoute\Common\InputSourceFactory;
use Mcustiel\PowerRoute\InputSources\Method;
use Mcustiel\PowerRoute\InputSources\Url;
use Mcustiel\PowerRoute\InputSources\Header;
use Mcustiel\PowerRoute\Matchers\Equals;
use Mcustiel\PowerRoute\Matchers\RegExp;
use Mcustiel\Phiremock\Server\Actions\AddExpectationAction;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\SapiStreamEmitter;

function getUriFromRequest(Request $request)
{
    $query = $request->getQuery();
    return 'http://localhost/' . $request->getPath() . (empty($query) ? '' : "?{$query}");
}

function getActionFactory()
{
    return new ActionFactory(['addExpectation' => AddExpectationAction::class]);
}

function getInputSourceFactory()
{
    return new InputSourceFactory([
        'method' => Method::class,
        'url' => Url::class,
        'header' => Header::class
    ]);
}

function getMatcherFactory()
{
    return new MatcherFactory([
        'isEqualTo' => Equals::class,
        'matchesPattern' => RegExp::class
    ]);
}

$powerRoute = new PowerRoute(
    require __DIR__ . '/../config/router-config.php',
    getActionFactory(),
    getInputSourceFactory(),
    getMatcherFactory()
);

$app = function (Request $request, Response $response) use ($powerRoute) {
    BufferedSink::createPromise($request)
        ->then(
            function ($body) use ($response, $request, $powerRoute) {
                $psrRequest = new ServerRequest(
                    array(),
                    array(),
                    getUriFromRequest($request),

                    $request->getQuery(),
                    $body,
                    array(),
                    array()
                );
                $psrResponse = $powerRoute->start($psrRequest, new \Zend\Diactoros\Response());

                $response->writeHead($psrResponse->getStatusCode(), $psrResponse->getHeaders());
                $response->end($psrResponse->getBody()->__toString());
            },
            function ($reason) {

            },
            function ($update) {

            }
        );
};

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http = new React\Http\Server($socket, $loop);

$http->on('request', $app);
echo "Server running at http://127.0.0.1:1337\n";

$socket->listen(1337);
$loop->run();




/* $app = new \Slim\Slim();
$requestBuilder = new RequestBuilder();
$stubs = Stubs();

$app->post(
    '/__expectation',
    function ($body) use ($app, $requestBuilder, $stubs) {
        $listOfErrors = [];
        try {
            $body = @json_decode($app->request->getBody(), true);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new Exception(json_last_error_msg());
            }

            $expectation = $requestBuilder->parseRequest(
                $body,
                Expectation::class,
                RequestBuilder::RETURN_ALL_ERRORS_IN_EXCEPTION
            );
            $stubs->addStub($expectation);
        } catch (\Mcustiel\SimpleRequest\Exception\InvalidRequestException $e) {
            $listOfErrors = $e->getErrors();
        } catch (\Exception $e) {
            $listOfErrors = [$e->getMessage()];
        }


        if (!empty($listOfErrors)) {
            header("Content-Type: application/json");
            return new HtmlResponse(json_encode($listOfErrors), 500);
        }
        echo $
    }
);

$app->any('/.*',
    function() use($app) {
        $app->redirect('/login');
    }
);

$app->run(); */
