<?php
namespace Mcustiel\Phiremock\Server\Domain;

use Mcustiel\SimpleRequest\Annotation\Validator as SRV;

class Response implements \JsonSerializable
{
    /**
     * @SRV\OneOf({@SRV\Type("integer"), @SRV\Not(@SRV\NotNull)})
     *
     * @var integer
     */
    private $statusCode;
    /**
     * @SRV\OneOf({@SRV\Type("string"), @SRV\Not(@SRV\NotNull)})
     *
     * @var string
     */
    private $body;
    /**
     * @SRV\OneOf({@SRV\Type("array"), @SRV\Not(@SRV\NotNull)})
     *
     * @var array
     */
    private $headers;
    /**
     * @SRV\OneOf({@SRV\Type("integer"), @SRV\Not(@SRV\NotNull)})
     *
     * @var integer
     */
    private $delayMillis;

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function getDelayMillis()
    {
        return $this->delayMillis;
    }

    public function setDelayMillis($delayMillis)
    {
        $this->delayMillis = $delayMillis;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'statusCode' => $this->statusCode,
            'body' => $this->body,
            'headers' => $this->headers,
            'delayMillis' => $this->delayMillis,
        ];
    }
}
