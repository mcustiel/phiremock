<?php
namespace Mcustiel\Phiremock\Server\Domain;

use Mcustiel\SimpleRequest\Annotation\Filter as SRF;
use Mcustiel\SimpleRequest\Annotation\Validator as SRV;
use Mcustiel\SimpleRequest\Annotation\ParseAs;

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
     * @ParseAs("\Mcustiel\Phiremock\Server\Domain\Condition")
     */
    private $url;
    /**
     * @var Condition
     *
     * @ParseAs("\Mcustiel\Phiremock\Server\Domain\Condition")
     */
    private $body;
    /**
     * @var Condition[]
     *
     * @SRV\Type("array")
     */
    private $headers;

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

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

    public function setUrl($url)
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

    public function setBody($body)
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

    public function setHeaders($headers)
    {
        if (!($headers instanceof \stdClass)) {
            //throw new HeaderParsingException();
        }
        $this->headers = $this->parseHeadersConditions($headers);
        return $this;
    }

    private function parseHeadersConditions(array $headers)
    {
        if ($headers) {
            return $this->createConditionsArray($headers);
        }
        //throw new HeaderParsingException();
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
