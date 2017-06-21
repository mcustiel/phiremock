<?php

namespace Mcustiel\Phiremock\Domain;

use Mcustiel\SimpleRequest\Annotation\Filter as SRF;
use Mcustiel\SimpleRequest\Annotation\Validator as SRV;

class Response implements \JsonSerializable
{
    /**
     * @SRF\DefaultValue(200)
     * @SRV\OneOf({
     *      @SRV\Type("integer"),
     *      @SRV\Not(@SRV\NotNull)
     * })
     *
     * @var int
     */
    private $statusCode = 200;
    /**
     * @SRV\OneOf({
     *      @SRV\Type("string"),
     *      @SRV\Not(@SRV\NotNull)
     * })
     *
     * @var string
     */
    private $body;
    /**
     * @SRV\OneOf({
     *      @SRV\Type("array"),
     *      @SRV\Not(@SRV\NotNull)
     * })
     *
     * @var array
     */
    private $headers;
    /**
     * @SRV\OneOf({
     *      @SRV\AllOf({
     *          @SRV\Type("integer"),
     *          @SRV\Minimum(0)
     *      }),
     *      @SRV\Not(@SRV\NotNull)
     * })
     *
     * @var int
     */
    private $delayMillis;

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @return string
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
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return int
     */
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
            'statusCode'  => $this->statusCode,
            'body'        => $this->body,
            'headers'     => $this->headers,
            'delayMillis' => $this->delayMillis,
        ];
    }
}
