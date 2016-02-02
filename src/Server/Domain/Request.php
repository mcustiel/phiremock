<?php
namespace Mcustiel\Phiremock\Server\Domain;

use Mcustiel\SimpleRequest\Annotation\Filter as SRF;
use Mcustiel\SimpleRequest\Annotation\Validator as SRV;

class Request
{
    /**
     * @var string
     *
     * @SRF\LowerCase
     * @SRV\Type("string")
     * @SRV\Enum({"get", "post", "put", "delete", "fetch", "options"})
     */
    private $method;
    /**
     * @var Condition
     *
     * @SRF\CustomFilter(class="\Mcustiel\Phiremock\Server\Http\RequestFilters\ConvertToCondition")
     */
    private $url;
    /**
     * @var Condition
     *
     * @SRF\CustomFilter(class="\Mcustiel\Phiremock\Server\Http\RequestFilters\ConvertToCondition")
     */
    private $body;
    /**
     * @var Condition[]
     *
     * @SRF\CustomFilter(class="\Mcustiel\Phiremock\Server\Http\RequestFilters\HeadersConditionsFilter")
     */
    private $headers;

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return \Mcustiel\Phiremock\Server\Domain\Request
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return \Mcustiel\Phiremock\Server\Domain\Condition
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param \Mcustiel\Phiremock\Server\Domain\Condition $url
     * @return \Mcustiel\Phiremock\Server\Domain\Request
     */
    public function setUrl(Condition $url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return \Mcustiel\Phiremock\Server\Domain\Condition
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param \Mcustiel\Phiremock\Server\Domain\Condition $body
     * @return \Mcustiel\Phiremock\Server\Domain\Request
     */
    public function setBody(Condition $body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return \Mcustiel\Phiremock\Server\Domain\Condition[]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param \Mcustiel\Phiremock\Server\Domain\Condition[]  $headers
     * @return \Mcustiel\Phiremock\Server\Domain\Request
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    private function createConditionsArray(array $headers)
    {
        $return = [];
        foreach($headers as $key => $conditionArray) {
            if (preg_match('/[a-z][a-z1-2\-]+/i', $key) && $conditionArray instanceof \stdClass) {
                $return[$key] = $this->getConditionOrFail($conditionArray);
            }
        }
        return $return;
    }

    private function getConditionOrFail($conditionArray)
    {
        $matcher = key((array)$conditionArray);
        $value = current((array)$conditionArray);
        if (empty($matcher) && empty($value)) {
            //throw new HeaderParsingException();
        }
        return new Condition($matcher, $value);
    }
 }
