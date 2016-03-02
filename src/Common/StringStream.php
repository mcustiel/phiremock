<?php
namespace Mcustiel\Phiremock\Common;

use Zend\Diactoros\Stream;

class StringStream extends Stream
{
    public function __construct($string)
    {
        parent::__construct('data://text/plain;base64,' . base64_encode($string));
    }
}
