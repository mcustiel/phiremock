<?php
namespace Mcustiel\Phiremock\Domain;

class Condition implements \JsonSerializable
{
    /**
     * @var string
     */
    private $matcher;
    /**
     * @var mixed
     */
    private $value;

    public function __construct($matcher = null, $value = null)
    {
        $this->matcher = $matcher;
        $this->value = $value;
    }

    public function getMatcher()
    {
        return $this->matcher;
    }

    public function setMatcher($matcher)
    {
        $this->matcher = $matcher;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function jsonSerialize()
    {
        return [$this->matcher => $this->value];
    }
}
