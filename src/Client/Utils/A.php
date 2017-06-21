<?php

namespace Mcustiel\Phiremock\Client\Utils;

class A
{
    /**
     * @return \Mcustiel\Phiremock\Client\Utils\RequestBuilder
     */
    public static function getRequest()
    {
        return RequestBuilder::create('get');
    }

    /**
     * @return \Mcustiel\Phiremock\Client\Utils\RequestBuilder
     */
    public static function postRequest()
    {
        return RequestBuilder::create('post');
    }

    /**
     * @return \Mcustiel\Phiremock\Client\Utils\RequestBuilder
     */
    public static function putRequest()
    {
        return RequestBuilder::create('put');
    }

    /**
     * @return \Mcustiel\Phiremock\Client\Utils\RequestBuilder
     */
    public static function optionsRequest()
    {
        return RequestBuilder::create('options');
    }

    /**
     * @return \Mcustiel\Phiremock\Client\Utils\RequestBuilder
     */
    public static function headRequest()
    {
        return RequestBuilder::create('head');
    }

    /**
     * @return \Mcustiel\Phiremock\Client\Utils\RequestBuilder
     */
    public static function fetchRequest()
    {
        return RequestBuilder::create('fetch');
    }

    /**
     * @return \Mcustiel\Phiremock\Client\Utils\RequestBuilder
     */
    public static function deleteRequest()
    {
        return RequestBuilder::create('delete');
    }

    /**
     * @return \Mcustiel\Phiremock\Client\Utils\RequestBuilder
     */
    public static function patchRequest()
    {
        return RequestBuilder::create('patch');
    }
}
