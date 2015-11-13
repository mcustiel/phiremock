<?php
namespace Mcustiel\Phiremock\Server\Domain;

use Mcustiel\SimpleRequest\Annotation\Validator as SRV;
use Mcustiel\SimpleRequest\Annotation\ParseAs;

class Expectation
{
    /**
     * @var Request
     *
     * @SRV\NotNull
     * @ParseAs("\\Mcustiel\\Phiremock\\Server\\Domain\\Request")
     */
    private $request;
    /**
     * @var Response
     *
     * @ParseAs("\\Mcustiel\\Phiremock\\Server\\Domain\\Response")
     */
    private $response;
    /**
     * @var string
     *
     * @SRV\OneOf({@SRV\Type("null"), @AllOf(@SRV\Type("string"), @SRV\NotEmpty)})
     */
    private $setScenario;
    /**
     * @var string
     *
     * @SRV\OneOf({@SRV\Type("null"), @AllOf(@SRV\Type("string"), @SRV\NotEmpty)})
     */
    private $scenarioIs;
}
