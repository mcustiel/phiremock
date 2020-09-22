# Phiremock Bundle

Phiremock is a mocker and stubber of HTTP services, it allows software developers to mock HTTP requests and setup responses to avoid calling real services during development, and is particulary useful during acceptance testing when expected http requests can be mocked and verified. Any HTTP service (i.e.: REST services) can be mocked and stubbed with Phiremock.
Phiremock is heavily inspired by [WireMock](http://wiremock.org/), but does not force you to have a java installation in your PHP development environment. The full functionality of Phiremock is detailed in the following list:
* Allows to mock http request based in method, headers, url and body content. 
* Allows to match expectations using regexp patterns or equality. 
* REST interface to setup.
* Stateful and stateless mocking.
* Network latency simulation.
* Priorizable expectations for cases in which more than one matches the request. If more than one expectation matches the request and no priorities were set, the first match is returned.
* Allows to verify the amount of times a request was done.
* Allows to load default expectations from json files in a directory tree.
* Proxy requests to another URL as they are received.
* Client with fluent interface to configure Phiremock.
* Integration to codeception through [phiremock-codeception-extension](https://github.com/mcustiel/phiremock-codeception-extension).
* Fill the response body using data from the request.

[![Latest Stable Version](https://poser.pugx.org/mcustiel/phiremock/v/stable)](https://packagist.org/packages/mcustiel/phiremock)
[![Build Status](https://scrutinizer-ci.com/g/mcustiel/phiremock/badges/build.png?b=master)](https://scrutinizer-ci.com/g/mcustiel/phiremock/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mcustiel/phiremock/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mcustiel/phiremock/?branch=master)
[![Monthly Downloads](https://poser.pugx.org/mcustiel/phiremock/d/monthly)](https://packagist.org/packages/mcustiel/phiremock)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/55feb317-7a46-4858-8634-31bfc89d7709/big.png)](https://insight.sensiolabs.com/projects/55feb317-7a46-4858-8634-31bfc89d7709)

**Note**: In version 2 Phiremock has separated libraries for the server and the client. This repository became the bundle for both libraries.

## Installation

### Composer:

This project is published in packagist, so you just need to add it as a dependency in your composer.json:

```json
    "require-dev": {
        "mcustiel/phiremock": "v2.0",
        "guzzlehttp/guzzle": "^6.0"
    }
```

## See

* Phiremock Server: https://github.com/mcustiel/phiremock-server
* Phiremock Client: https://github.com/mcustiel/phiremock-client

## Thanks to:

* Denis Rudoi ([@drudoi](https://github.com/drudoi))
* Henrik Schmidt ([@mrIncompetent](https://github.com/mrIncompetent))
* Nils Gajsek ([@linslin](https://github.com/linslin))
* Florian Levis ([@Gounlaf](https://github.com/Gounlaf))

And [everyone](https://github.com/mcustiel/phiremock/graphs/contributors) who submitted their Pull Requests.
