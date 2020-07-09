<?php

namespace Helper;

class AcceptanceV2 extends \Codeception\Module
{
    public function getPhiremockRequest(array $request): array
    {
        return array_merge(['version' => '2'], $request);
    }
}
