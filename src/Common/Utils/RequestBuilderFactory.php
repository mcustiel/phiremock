<?php

namespace Mcustiel\Phiremock\Common\Utils;

use Mcustiel\SimpleRequest\ParserGenerator;
use Mcustiel\SimpleRequest\RequestBuilder as SimpleRequestBuilder;
use Mcustiel\SimpleRequest\Services\DoctrineAnnotationService;
use Mcustiel\SimpleRequest\Services\PhpReflectionService;
use Mcustiel\SimpleRequest\Strategies\AnnotationParserFactory;
use Symfony\Component\Cache\Adapter\FilesystemAdapter as Psr6CacheAdapter;

class RequestBuilderFactory
{
    public static function createRequestBuilder()
    {
        return new SimpleRequestBuilder(
            new Psr6CacheAdapter(
                'requests',
                3600,
                sys_get_temp_dir() . '/phiremock/cache/'
            ),
            new ParserGenerator(
                new DoctrineAnnotationService(),
                new AnnotationParserFactory(),
                new PhpReflectionService()
            )
        );
    }
}
