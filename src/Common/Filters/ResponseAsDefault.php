<?php

namespace Mcustiel\Phiremock\Common\Filters;

use Mcustiel\Phiremock\Domain\Response;
use Mcustiel\SimpleRequest\Interfaces\FilterInterface;

class ResponseAsDefault implements FilterInterface
{
    public function filter($value)
    {
        if (empty($value)) {
            return new Response();
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\SimpleRequest\Interfaces\Specificable::setSpecification()
     * @SuppressWarnings("unused")
     */
    public function setSpecification($specification = null)
    {
    }
}
