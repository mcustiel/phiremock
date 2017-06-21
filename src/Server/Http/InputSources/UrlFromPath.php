<?php

namespace Mcustiel\Phiremock\Server\Http\InputSources;

use Mcustiel\PowerRoute\InputSources\InputSourceInterface;
use Psr\Http\Message\ServerRequestInterface;

class UrlFromPath implements InputSourceInterface
{
    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\PowerRoute\InputSources\InputSourceInterface::getValue()
     */
    public function getValue(ServerRequestInterface $request, $argument = null)
    {
        $url = $request->getUri();
        $return = $url->getPath();
        if ($url->getQuery()) {
            $return .= '?' . $url->getQuery();
        }
        if ($url->getFragment()) {
            $return .= '#' . $url->getFragment();
        }

        return $return;
    }
}
