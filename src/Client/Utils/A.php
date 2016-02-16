<?php
namespace Mcustiel\Phiremock\Client\Utils;

class A
{
    public static function getRequest()
    {
        return RequestBuilder::create('get');
    }

    public static function postRequest()
    {
        return RequestBuilder::create('post');
    }

    public static function putRequest()
    {
        return RequestBuilder::create('put');
    }

    public static function optionsRequest()
    {
        return RequestBuilder::create('options');
    }

    public static function headRequest()
    {
        return RequestBuilder::create('head');
    }

    public static function fetchRequest()
    {
        return RequestBuilder::create('fetch');
    }
}
