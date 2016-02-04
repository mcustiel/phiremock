<?php
namespace Mcustiel\Phiremock\Server\Domain;

use Mcustiel\SimpleRequest\Annotation\Filter as SRF;
use Mcustiel\SimpleRequest\Annotation\Validator as SRV;

class Request implements \JsonSerializable
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

    /**
     * @param \Mcustiel\Phiremock\Server\Domain\Condition $body
     * @return \Mcustiel\Phiremock\Server\Domain\Request
     */
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

    /**
     * @param \Mcustiel\Phiremock\Server\Domain\Condition[]  $headers
     * @return \Mcustiel\Phiremock\Server\Domain\Request
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'method' => $this->method,
            'url' => $this->url,
            'body' => $this->body,
            'headers' => $this->headers,
        ];
    }
}
