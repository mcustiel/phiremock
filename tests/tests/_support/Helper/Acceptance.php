<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
    /**
     * Print debug message to the screen.
     *
     * @param string $message
     */
    public function writeDebugMessage($message)
    {
        $this->debug($message);
    }
}
