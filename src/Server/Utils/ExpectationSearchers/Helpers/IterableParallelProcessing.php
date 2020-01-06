<?php
/**
 * This file is part of Phiremock.
 *
 * Phiremock is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Phiremock is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Phiremock.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Mcustiel\Phiremock\Server\Utils\ExpectationSearchers\Helpers;

class IterableParallelProcessing
{
    const CHILD_STREAM_INDEX = 1;
    const PARENT_STREAM_INDEX = 0;
    const PROCESSES_WAIT_MICROSECONDS = 3000;
    /** @var int */
    private $parallelCount;

    public function __construct($parallelCount)
    {
        $this->parallelCount = $parallelCount;
    }

    public function execute($iterable, callable $function)
    {
        $pids = [];
        $result = [];
        $socketsCollection = [];

        foreach ($iterable as $key => $value) {
            $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
            $socketsCollection[$key] = $sockets;
            $pid = pcntl_fork();
            if ($pid == -1) {
                die('Could not fork');
            } else if ($pid) {
                $this->setUpParentStreams($sockets);
                $pids[$key] = $pid;

                $this->waitForFreeSlots($pids, $socketsCollection, $result);
            } else {
                $this->runFunctionInChildProcess($function, $sockets, $key, $value);
            }
        }
        $this->waitForRemainingProcessesToFinish($pids, $socketsCollection, $result);
        return $this->getUnserializedResults($result);
    }

    private function getUnserializedResults($serializedResult)
    {
        $result = [];
        foreach ($serializedResult as $index => $serializedData) {
            $result[$index] = unserialize($serializedData);
        }
        return $result;
    }


    private function waitForFreeSlots(array &$pids, array &$socketsCollection, array &$result)
    {
        while (count($pids) >= $this->parallelCount) {
            $this->waitForProcesses($pids, $socketsCollection, $result);
        }
    }

    private function waitForRemainingProcessesToFinish(array &$pids, array &$socketsCollection, array &$result)
    {
        while (count($pids) > 0) {
            $this->waitForProcesses($pids, $socketsCollection, $result);
        }
    }

    private function setUpParentStreams(array $sockets)
    {
        fclose($sockets[self::PARENT_STREAM_INDEX]);
        stream_set_blocking($sockets[self::CHILD_STREAM_INDEX], false);
    }

    private function runFunctionInChildProcess(callable $function, array $sockets, $key, $value)
    {
        fclose($sockets[self::CHILD_STREAM_INDEX]);
        $return = $function($key, $value);
        fputs($sockets[self::PARENT_STREAM_INDEX], serialize($return));
        fclose($sockets[self::PARENT_STREAM_INDEX]);
        exit(0);
    }

    private function waitForProcesses(array &$pids, array &$socketsCollection, array &$result)
    {
        foreach($pids as $index => $pid) {
            $this->getExecutionResult($index, $socketsCollection, $result);
            $status = 0;
            $res = pcntl_waitpid($pid, $status, WNOHANG);
            $processExited = $res == -1 || $res > 0;
            if($processExited) {
                fclose($socketsCollection[$index][self::CHILD_STREAM_INDEX]);
                unset($socketsCollection[$index]);
                unset($pids[$index]);
            }
        }
        usleep(self::PROCESSES_WAIT_MICROSECONDS);
    }

    private function getExecutionResult($index, array $socketsCollection, array &$result)
    {
        if (isset($socketsCollection[$index])) {
            while(($string = fgets($socketsCollection[$index][self::CHILD_STREAM_INDEX])) !== false) {
                $result[$index] .= $string;
            }
            echo  PHP_EOL . PHP_EOL;
        }
    }
}


