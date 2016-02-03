<?php
namespace Mcustiel\Phiremock\Server\Converters;

use Mcustiel\Conversion\Converter;
use Zend\Diactoros\Response\HtmlResponse;

class ExpectationResponseToHttpResponseConverter implements Converter
{
    /**
     * @param \Mcustiel\Phiremock\Server\Domain\Response $object
     */
    public function convert($object)
    {
        return new HtmlResponse($object->getBody(), $object->getStatusCode(), $object->getHeaders());
    }
}
