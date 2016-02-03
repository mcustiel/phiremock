<?php
namespace Mcustiel\Phiremock\Server;

use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;

interface StorageInterface extends ExpectationStorage, ScenarioStorage
{
}
