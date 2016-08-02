<?php
namespace Mcustiel\Phiremock\Common\Utils;

use Mcustiel\SimpleRequest\RequestBuilder as SimpleRequestBuilder;
use Symfony\Component\Cache\Adapter\FilesystemAdapter as Psr6CacheAdapter;
use Mcustiel\SimpleRequest\ParserGenerator;
use Mcustiel\SimpleRequest\Services\DoctrineAnnotationService;
use Mcustiel\SimpleRequest\Strategies\AnnotationParserFactory;
use Mcustiel\SimpleRequest\Services\PhpReflectionService;

class RequestBuilderFactory
{
    public static function createRequestBuilder()
    {
        return new SimpleRequestBuilder(
            new Psr6CacheAdapter(
                'phiremock',
                3600,
                sys_get_temp_dir() . '/phiremock/cache/requests/'
            ),
            new ParserGenerator(
                new DoctrineAnnotationService(),
                new AnnotationParserFactory(),
                new PhpReflectionService()
            )
        );
    }
}
