<?php

namespace Helper;

class AcceptanceV1 extends \Codeception\Module
{
    public function writeDebugMessage(string $message): void
    {
        $this->debug($message);
    }

    public function getPhiremockRequest(array $request): array
    {
        return $request;
    }
}
