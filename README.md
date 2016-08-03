# Phiremock

Phiremock is a HTTP services mocker and stubber, it allows software developers to mock HTTP requests and setup static responses to avoid calling real services during development. Also can be used to setup the responses to expected requests during acceptance testing. Any HTTP service (i.e.: REST services) can be mocked and stubbed with Phiremock.
Phiremock is heavily inspired by [WireMock](http://wiremock.org/), but does not force you to have a java installation in your PHP development environment. The full functionality of Phiremock is detailed in the following list:
 
* Allows to mock http request based in method, headers, url and body content. 
* Allows to match expectations using regexp patterns or equality. 
* REST interface to setup.
* Stateful and stateless mocking.
* Network latency simulation.
* Priorizable expectations for cases in which more than one matches the request. If more than one expectation matches the request and no priorities were set, the first match is returned.
* Allows to verify the amount of times a request was done.
* Allows to load default expectations from json files in a directory.
* Proxy requests to another URL as they are received.
* Client with fluent interface to configure Phiremock.
* Integration to codeception through [phiremock-codeception-extension](https://github.com/mcustiel/phiremock-codeception-extension).
* Fill the response body using data from the request.

[![Latest Stable Version](https://poser.pugx.org/mcustiel/phiremock/v/stable)](https://packagist.org/packages/mcustiel/phiremock)
[![Build Status](https://scrutinizer-ci.com/g/mcustiel/phiremock/badges/build.png?b=master)](https://scrutinizer-ci.com/g/mcustiel/phiremock/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mcustiel/phiremock/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mcustiel/phiremock/?branch=master)
[![Monthly Downloads](https://poser.pugx.org/mcustiel/phiremock/d/monthly)](https://packagist.org/packages/mcustiel/phiremock)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/55feb317-7a46-4858-8634-31bfc89d7709/big.png)](https://insight.sensiolabs.com/projects/55feb317-7a46-4858-8634-31bfc89d7709)

## Installation

### Composer:

This project is published in packagist, so you just need to add it as a dependency in your composer.json:

```json
    "require-dev": {
        "mcustiel/phiremock": "*"
    },
    "minimum-stability": "dev"
```

> *NOTE*
> Phiremock uses a dev-master version of react/http to work. Because of this, until ReactPhp guys tag a new 
> version you will need to set your project's minimum stability to dev to be able to install Phiremock. 

### Phar:

You can also download the standalone phar application from [here](https://github.com/mcustiel/phiremock/releases/download/v1.3.0/phiremock.phar).

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

or

```bash
    ./phiremock.phar -p 8088 -i 0.0.0.0
```

**Cli arguments:** 
* -i argument: specifies in which interface Phiremock should listen for requests. Default is 0.0.0.0
* -p argument: is the port in which Phiremock should listen. Default is 8086
* -d argument: enables debug mode in logger. By default, info logging level is used.
* -e argument: specifies a directory to search for json files defining expectations to load by default. Default is ~/.phiremock/expectations

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
            "matches" : "/some regex pattern/i"
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
    },
    "proxyTo": null,
    "priority" : 0
}
```

The same format can be used in expectation files saved in the directory specified by the -e argument of the CLI. For Phiremock to be able to load them, each file should have `.json` extension.

## Phiremock Client 
Phiremock also provides a handy client object to simplify communication with the server in a fluent way.

## Features

### Create an expectation 
To create previous response from code the following should be used:

#### Phiremock client
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
#### API call:
```
POST /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "request": {
        "method": "GET",
        "url": {
            "isEqualTo" : "/example_service/some/resource"
        }
    },
    "response": {
        "statusCode": 200,
        "body": "{\"id\": 1, \"description\": \"I am a resource\"}",
        "headers": {
            "Content-Type": "application/json"
        }
    }
}
```

### Clear expectations
After a test runs, all previously configured expectations can be deleted so they don't affect the execution of the next test:

```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    $phiremock->clearExpectations();
``` 
#### API call:
```
DELETE /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host

```

### List all expectations
If you want, for some reason, list all created expectations. A convenient method is provided:

```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    $expectations = $phiremock->listExpectations();
    
    foreach ($expectations as $expectation) {
        var_export($expectation);
    }
```  
#### API call:
```
GET /__phiremock/expectations HTTP/1.1
Host: your.phiremock.host

```

### Verify requests
To know how much times a request was sent to Phiremock, for instance to verify after a feature execution in a test, there is a helper method too:

```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    $actualExecutions = $phiremock->countExecutions(
        A::getRequest()->andUrl(Is::equalTo('/example_service/some/resource'))
    );
    $this->assertEquals($expectedExecutions, $actualExecutions);
```
#### API call:
```
POST /__phiremock/executions HTTP/1.1
Host: your.phiremock.host
Content-Type: application/json

{
    "request": {
        "method": "GET",
        "url": {
            "isEqualTo" : "/example_service/some/resource"
        }
    },
    "response": {}
}
```

### Reset requests log
To reset the requests counter to 0, Phiremock also provides a method: 

```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    $phiremock->resetRequestsCounter();
```
#### API call:
```
DELETE /__phiremock/executions HTTP/1.1
Host: your.phiremock.host
```

## Cool stuff

### Priorities
Phiremock accepts multiple expectations that can match the same request. If no priorities are set, it will match the first expectation created but, if you need to give high priority to some request, you can do it easily.

 ```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    
    $expectation = Phiremock::on(
        A::getRequest()->andUrl(Is::equalTo('/example_service/some/resource'))
    )->then(
        Respond::withStatusCode(200)
            ->andBody('<resource id="1" description="I am a resource"/>')
            ->andHeader('Content-Type', 'text/xml')
    );
    $phiremock->createExpectation($expectation);
    
    $expectation = Phiremock::on(
        A::getRequest()->andUrl(Is::equalTo('/example_service/some/resource'))
            ->andHeader('Accept', Is::equalTo('application/json'))
            ->andPriority(1)
    )->then(
        Respond::withStatusCode(200)
            ->andBody('{"id": 1, "description": "I am a resource"}')
            ->andHeader('Content-Type', 'application/json')
    );
    $phiremock->createExpectation($expectation);
```

In the previous example, both expectations will match a request with url equal to: `/example_service/some/resource` and Accept header equal to `application/json`. But Phiremock will give higher priority to the one with Accept header.
Default priority for an expectation is 0. 

### Stateful behaviour
If you want to simulate a behaviour of the service in which a response depends of a state that was set in a previous request. You can use scenarios to create a stateful behaviour.

```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    
    $expectation = Phiremock::on(
        A::posttRequest()->andUrl(Is::equalTo('/example_service/some/resource'))
            ->andBody(Is::equalTo('{"id": "1", "name" : "resource"}'))
            ->andHeader('Content-Type', Is::equalTo('application/json'))
            ->andScenarioState('saved', 'Scenario.START')
    )->then(
        Respond::withStatusCode(201)
            ->andBody('{"id": "1", "name" : "resource"}')
            ->andHeader('Content-Type', 'application/json')
            ->andSetScenarioStateTo('RESOURCE_SAVED')
    );
    $phiremock->createExpectation($expectation);
    
    $expectation = Phiremock::on(
        A::getRequest()->andUrl(Is::equalTo('/example_service/some/resource'))
            ->andBody(Is::equalTo('{"id": "1", "name" : "resource"}'))
            ->andHeader('Content-Type', Is::equalTo('application/json'))
            ->andScenarioState('saved', 'RESOURCE_SAVED')
    )->then(
        Respond::withStatusCode(409)
            ->andBody('Resource with id = 1 was already created')
    );
    $phiremock->createExpectation($expectation);
```

In this case, Phiremock will execute the first expectation for the first call, and the second one for the second call even when both requests matchers are exactly the same.
If you want after the second call, to go back to the initial state just add `->andSetScenarioStateTo('Scenario.START')` to the response.

To reset all scenarios to the initial state (Scenario.START) use this simple method from the client: 

```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    
    $phiremock->resetScenarios();
```
#### API call:
```
DELETE /__phiremock/scenarios HTTP/1.1
Host: your.phiremock.host
```

### Netwok latency simulation
If you want to test how your application behaves on, for instance, a timeout; you can make Phiremock to delay the response of your request as follows.

```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    
    $expectation = Phiremock::on(
        A::posttRequest()->andUrl(Is::equalTo('/example_service/some/resource'))
            ->andBody(Is::equalTo('{"id": "1", "name" : "resource"}'))
            ->andHeader('Content-Type', Is::equalTo('application/json'))
    )->then(
        Respond::withStatusCode(200)->andDelayInMillis(30000)
    );
    $phiremock->createExpectation($expectation);
```
This will wait 30 seconds before sending the response.

### Proxy
It could be the case a mock is not needed for certain call. For this specific case, Phiremock provides a proxy feature that will pass the received request unmodified to a configured URI. It can be used as folows:

```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    
    $expectation = Phiremock::on(
        A::posttRequest()->andUrl(Is::equalTo('/example_service/proxy/me'))
            ->andBody(Is::equalTo('{"id": "1", "name" : "resource"}'))
            ->andHeader('Content-Type', Is::equalTo('application/json'))
    )->proxyTo(
        'http://your.real.service/some/path/script.php'
    );
    $phiremock->createExpectation($expectation);
```
In this case, Phiremock will POST `http://your.real.service/some/path/script.php` with the configured body and header and return it's response.

### Generate response body based in request data
It could happen that you want to make your response dependent on data you receive in your request. For this cases
you can use regexp matching for request url and/or body, and access the subpatterns matches from your response body specification
using `${body.matchIndex}` or `${url.matchIndex}` notation.

#### Example:

```php
    use Mcustiel\Phiremock\Client\Phiremock;

    $phiremock = new Phiremock('phiremock.server', '8080');
    
    $expectation = Phiremock::on(
        A::posttRequest()->andUrl(Is::matching('~/example_service/(\w+)/?id=(\d+)~'))
            ->andBody(Is::matching('~\{"name" : "([^"]+)"\}~'))
            ->andHeader('Content-Type', Is::equalTo('application/json'))
    )->then(
        Respond::withStatusCode(200)->andBody('The resource is ${url.1}, the id is ${url.2) and the name is ${body.1}')
    );
    $phiremock->createExpectation($expectation);
```
## Apendix

### List of condition matchers:

* **contains:** Checks that the given section of the http request contains the specified string.
* **isEqualTo:** Checks that the given section of the http request is equal to the one specified, case sensitive.
* **isSameString:** Checks that the given section of the http request is equal to the one specified, case insensitive.
* **matches:** Checks that the given section of the http request matches the regular expression specified.

### Contributing:

Fork the project and pull request, as easy as that. The code standard used is based in PSR, just run php-cs-fixer with this configuration: 
```
--level=psr2 --fixers=-psr0,no_empty_lines_after_phpdocs,no_blank_lines_after_class_opening,operators_spaces,phpdoc_params,phpdoc_scalar,unused_use,align_double_arrow,multiline_array_trailing_comma,list_commas,phpdoc_separation,single_quote
```
