<?php

namespace Helper;

class AcceptanceV2 extends \Codeception\Module
{
    public function getPhiremockRequest(array $request): array
    {
        if (isset($request['request']['method'])) {
            $request['request']['method'] = ['isSameString' => $request['request']['method']];
        }
        return array_merge(['version' => '2'], $request);
    }
}
