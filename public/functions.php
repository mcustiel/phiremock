<?php

use Mcustiel\PowerRoute\Common\Factories\ActionFactory;
use Mcustiel\PowerRoute\Common\Factories\MatcherFactory;
use Mcustiel\PowerRoute\Common\Factories\InputSourceFactory;
use Mcustiel\PowerRoute\InputSources\Method;
use Mcustiel\PowerRoute\InputSources\Url;
use Mcustiel\PowerRoute\InputSources\Header;
use Mcustiel\PowerRoute\Matchers\Equals;
use Mcustiel\PowerRoute\Matchers\RegExp;
use Mcustiel\Phiremock\Server\Actions\AddExpectationAction;
use Mcustiel\PowerRoute\Actions\ServerError;
use Mcustiel\PowerRoute\Common\Conditions\ConditionsMatcherFactory;
use Mcustiel\Phiremock\Server\Actions\SearchRequestAction;

function getActionFactory($requestBuilder, $storage)
{
    return new ActionFactory([
        'addExpectation' => new AddExpectationAction($requestBuilder, $storage),
        //'listExpectations' => new ListExpectationAction($storage),
        'serverError' => [ServerError::class],
        'parseExpectations' => new SearchRequestAction($storage, new RequestExpectationComparator())
    ]);
}

function getInputSourceFactory()
{
    return new InputSourceFactory([
        'method' => [Method::class],
        'url' => [Url::class],
        'header' => [Header::class],
    ]);
}

function getMatcherFactory()
{
    return new MatcherFactory([
        'isEqualTo' => [Equals::class],
        'matchesPattern' => [RegExp::class],
    ]);
}

function getConditionsMatchersFactory()
{
    return new ConditionsMatcherFactory(
        getInputSourceFactory(),
        getMatcherFactory()
    );
}
