# Phiremock (beta)

Phiremock is a HTTP services mocker and stubber, it allows software developers to setup static responses for expected requests and avoid calling real services during development. Also can be used to setup the responses to expected requests during acceptance testing. Any HTTP service (i.e.: REST services) can be mocked and stubbed with Phiremock.

## Installation

### Composer:

This project is published in packagist, so you just need to add it as a dependency in your composer.json:

```json
    "require": {
        "mcustiel/phiremock": "*"
    }
```

### Phar:

You can also download the standalone phar application from [here](./phiremock.phar).

## How does it work?

Phiremock will allow you to create a stubbed version of some external service your application needs to communicate to. That can be used to avoid calling the real application during development or to setup responses to expected requests

First of all you need to setup the config for the different environments for your application. For instance:

```json
    // config/production.json
    {
        "external_service": "https://service.example.com/v1/"
    }
```

```json
    // config/acceptance.json
    {
        "external_service": "https://phiremock.server:8080/example_service/"
    }
```

Run your phiremock service using it's cli command:

```bash
    ./vendor/bin/phiremock -p 8088 -i 0.0.0.0
```

**Cli arguments:** 
* -i argument specifies in which interface Phiremock should listen for requests. Default is 0.0.0.0
* -p argument is the port in which Phiremock should listen. Default is 8086
* -d argument enables debug mode in logger. By default, info logging level is used.
* -e argument specifies a directory to search for json files defining expectations to load by default. Default is ~/.phiremock/expectations

Then, using phiremock's REST interface, expectations can be configured, specifying the response to send for a given request. A REST expectation resource for phiremock looks like this:

```json
{
    "scenarioName": "potato",
    "scenarioStateIs": "Scenario.START",
    "newScenarioState": "tomato",
    "request": {
        "method": "GET",
        "url": {
            "isEqualTo" : "/example_service/some/resource"
        },
        "body" : {
            "matches" : '/some regex pattern/i'
        },
        "headers" : {
            "X-MY-HEADER": "Some value"
        }
    },
    "response": {
        "statusCode": 200,
        "body": "{\"id\": 1, \"description\": \"I am a resource\"}",
        "headers": {
            "Content-Type": "application/json"
        },
        "delayMillis": 3000
    }
}
```

The same format can be used in expectation files saved in the directory specified by the -e argument of the CLI. For Phiremock to be able to load them, each file should have `.json` extension.

## Phiremock Client 

Phiremock provides a handy client object to simplify communication with the server. To create previous response from code the following should be used:

```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    $expectation = Phiremock::on(
        A::getRequest()->andUrl(Is::equalTo('/example_service/some/resource'))
    )->then(
        Respond::withStatusCode(200)
            ->andBody('{"id": 1, "description": "I am a resource"}')
            ->andHeader('Content-Type', 'application/json')
    );
    $phiremock->createExpectation($expectation);
```

After a test runs, all previously configured expectations can be deleted so they don't affect the execution of the next test:

```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    $phiremock->clearExpectations();
``` 

If you want, for some reason, list all created expectations. A convenient method is provided:

```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    $expectations = $phiremock->listExpectations();
    
    foreach ($expectations as $expectation) {
        var_export($expectation);
    }
``` 

```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    $expectations = $phiremock->listExpectations();
    
    foreach ($expectations as $expectation) {
        var_export($expectation);
    }
``` 

To know how much times a request was sent to Phiremock, for instance to verify after a feature execution in a test, there is a helper method too:

```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    $actualExecutions = $phiremock->countExecutions(
        A::getRequest()->andUrl(Is::equalTo('/example_service/some/resource'))
    );
    $this->assertEquals($expectedExecutions, $actualExecutions);
```

To reset the requests counter to 0, Phiremock also provides a method: 

```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    $phiremock->resetRequestsCounter();
``` 

Phiremock is heavily inspired by [WireMock](http://wiremock.org/), but does not force you to have a java installation in your PHP development environment. The full functionality of Phiremock is detailed in the following list:
 
* Allows to mock http request based in method, headers, url and body content. 
* Allows to match expectations using regexp patterns or equality. 
* REST interface to setup.
* Stateful and stateless mocking.
* Network latency simulation.
* Priorizable expectations for cases in which more than one matches the request. If more than one expectation matches the request and no priorities were set, the first match is returned.
* Allows to verify the amount of times a request was done.
* Allows to load default expectations from json files in a directory.
