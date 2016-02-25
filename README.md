# phiremock (beta)

Phiremock is a HTTP services mocker and stubber, it allows software developers to setup static responses for expected requests to avoid connecting to real services during development. Also can be used to avoid using real services and to expect always the same response during acceptance tests. Any HTTP service (i.e.: REST services) can be mocked and stubbed.

## How does it work?

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
    ./bin/phiremock -p 8088 -i 0.0.0.0
```

**Cli arguments:** 
* -i argument specifies in which interface Phiremock should listen for requests. Default is 0.0.0.0
* -p argument is the port in which Phiremock should listen. Default is 8086

Then, using phiremock's REST interface, expectations can be configured, specifying the response to send for a given request. A REST expectation resource for phiremock looks like this:

```json
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

Phiremock is heavily inspired by [wiremock](http://wiremock.org/), but does not force you to have a java installation in your PHP development environment. The full functionality of Phiremock is detailed in the following list:
 
* Allows to mock http request based in method, headers, url and body content. 
* Allows to match expectations using regexp patterns or equality. 
* REST interface to setup.
* Stateful and stateless mocking.
* Network latency simulation.
* Priorizable expectations for cases in which more than one matches the request. If more than one expectation matches the request and no priorities were set, the first match is returned.
* Allows to verify the amount of times a request was done.

