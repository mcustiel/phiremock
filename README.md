# phiremock
A http mocker service, [wiremock](http://wiremock.org/) style, written in PHP. 
* Allows to mock http request based in method, headers, url and body content. 
* Allows to match expectations using regexp patterns or equality. 
* REST interface to setup.
* Stateful and stateless mocking.
* Network latency simulation.
* Priorizable expectations for cases in which more than one matches the request. If more than one expectation matches the request and no priorities were set, the first match is returned.
* Allows to verify the amount of times a request was done.

### Important
This project is still **WORK IN PROGRESS** but can be tested ;)
