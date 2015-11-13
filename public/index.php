<?php
use Mcustiel\Phiremock\Server\Domain\Expectation;
require __DIR__ . '/vendor/autoload.php';

$app = new \Slim\Slim();
$requestBuilder = new \Mcustiel\SimpleRequest\RequestBuilder();
$stubs = Stubs();

$app->post(
    '/__expectation',
    function ($body) use ($app, $requestBuilder) {
        try {
            $body = @json_decode($app->request->getBody());
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new Exception();
            }

            $expectation = $requestBuilder->parseRequest(
                $body,
                Expectation::class,
                RequestBuilder::RETURN_ALL_ERRORS_IN_EXCEPTION
            );
            $stubs->addExpectation($expectation);

        } catch (\Mcustiel\SimpleRequest\Exception\InvalidRequestException $e) {
            $listOfErrors = $e->getErrors();
        } catch (\Exception $e) {
            $listOfErrors = [$e->getMessage()];
        }
        //setting header before sending the JSON response back to the iPhone
        header("Content-Type: application/json");
        echo json_encode($new_body);
    }
);
$app->run();
