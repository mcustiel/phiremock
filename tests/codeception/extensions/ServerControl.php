<?php

namespace Mcustiel\Codeception\Extensions;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Symfony\Component\Process\Process;

class ServerControl extends \Codeception\Extension
{
    private const EXPECTATIONS_DIR = __DIR__ . '/../../tests/_data/expectations';

    public static $events = [
        Events::SUITE_BEFORE => 'suiteBefore',
        Events::SUITE_AFTER  => 'suiteAfter',
    ];

    /** @var Process */
    private $application;

    public function suiteBefore(SuiteEvent $event)
    {
        $this->writeln('Starting Phiremock server');

        $commandLine = [
            './vendor/bin/phiremock',
            '-d',
            '-e',
            self::EXPECTATIONS_DIR,
            '>',
            codecept_log_dir('phiremock.log'),
            '2>&1',
        ];
        $this->application = Process::fromShellCommandline(implode(' ', $commandLine));
        $this->writeln($this->application->getCommandLine());
        $this->application->start();
        sleep(1);
    }

    public function suiteAfter()
    {
        $this->writeln('Stopping Phiremock server');
        if (!$this->application->isRunning()) {
            return;
        }
        $this->application->stop(3);
    }
}
