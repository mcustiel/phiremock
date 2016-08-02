<?php
namespace Mcustiel\Phiremock\Common\Filters;

use Mcustiel\SimpleRequest\Interfaces\FilterInterface;
use Mcustiel\Phiremock\Domain\Response;

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
     * {@inheritDoc}
     *
     * @see \Mcustiel\SimpleRequest\Interfaces\Specificable::setSpecification()
     * @SuppressWarnings("unused")
     */
    public function setSpecification($specification = null)
    {
    }
}
