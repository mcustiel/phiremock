<?php

namespace Mcustiel\Phiremock\Server\Http\ResponseFilters;

use Mcustiel\SimpleRequest\Interfaces\FilterInterface;

class JsonToString implements FilterInterface
{
    /**
     * {@inheritDoc}
     *
     * @see \Mcustiel\SimpleRequest\Interfaces\Specificable::setSpecification()
     */
    public function setSpecification($specification = null)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @see \Mcustiel\SimpleRequest\Interfaces\FilterInterface::filter()
     */
    public function filter($value)
    {
        return is_array($value) || is_object($value) ? json_encode($value) : $value;
    }
}

