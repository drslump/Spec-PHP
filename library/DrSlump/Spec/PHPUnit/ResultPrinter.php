<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero General Public License as
//  published by the Free Software Foundation, either version 3 of the
//  License, or (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero General Public License for more details.
//
//  You should have received a copy of the GNU Affero General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.

namespace DrSlump\Spec\PHPUnit;

/**
 * Extends a PHPUnit Result Printer to adapt it for Spec
 *
 * @package     Spec\PHPUnit
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class ResultPrinter extends \PHPUnit_TextUI_ResultPrinter implements \PHPUnit_Framework_TestListener
{
    const PASSED = 1;
    const FAILED = 2;
    const ERROR = 3;
    const INCOMPLETE = 4;
    const SKIPPED = 5;


    protected $lastTestResult;
    protected $exceptions = array();
    protected $failures = array();
    protected $errors = array();
    protected $incomplete = array();
    protected $skipped = array();


    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->lastTestResult = self::ERROR;
        $this->errors[] = $e;
        $this->exceptions[] = $e;
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->lastTestResult = self::FAILED;
        $this->failures[] = $e;
        $this->exceptions[] = $e;
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->lastTestResult = self::INCOMPLETE;
        $this->incomplete[] = $e;
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->lastTestResult = self::SKIPPED;
        $this->skipped[] = $e;
    }

    /**
     * A suite started.
     *
     * @param \PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        $this->lastTestResult = self::PASSED;
    }

    /**
     * A test ended.
     *
     * @param \PHPUnit_Framework_Test $test
     * @param  $time
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
    }

    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
    }


    /**
     * Finish the reporting
     *
     */
    public function flush()
    {
        $this->printFailures();
    }

    /**
     * Prints the failures and errors found so far
     *
     */
    public function printFailures()
    {
        $this->write(PHP_EOL);
        if (count($this->exceptions)) {
            if (count($this->failures) && count($this->errors)) {
                $this->write('Failures/Errors:' . PHP_EOL);
            } elseif (count($this->failures)) {
                $this->write('Failures:' . PHP_EOL);
            } else {
                $this->write('Errors:' . PHP_EOL);
            }
            $this->write(PHP_EOL);
            foreach($this->exceptions as $idx => $ex) {
                $this->write('  ' . ($idx+1) . ') ' . $ex->getMessage() . PHP_EOL);
                $this->write(PHP_EOL);
            }
        }
    }


    /**
     * Print a stack trace
     *
     * @param  $defect
     */
    protected function printDefectTrace($defect)
    {
        $this->write($defect->getExceptionAsString() . "\n");

        // Get exception stack trace
        $exception = $defect->thrownException();
        if ($exception instanceof \PHPUnit_Framework_SyntheticError) {
            $trace = $exception->getSyntheticTrace();
        } else {
            $trace = $exception->getTrace();
        }

        // Add exception info to backtrace
        array_unshift($trace, array(
            'file'  => $exception->getFile(),
            'line'  => $exception->getLine(),
        ));


        $last = null;
        $result = array();
        // Unfiltered stackstrace if in debug mode
        $groups = empty($this->debug) ? array('DEFAULT', 'PHPUNIT') : array();
        $filter = \PHP_CodeCoverage_Filter::getInstance();
        foreach ($trace as $frame) {
            if (isset($frame['file']) && isset($frame['line']) &&
                !$filter->isFiltered($frame['file'], $groups, TRUE)) {

                if (0 === strpos($frame['file'], TestRunner::SCHEME)) {
                    $frame['file'] = substr($frame['file'], strlen(TestRunner::SCHEME . '://'));
                    // expect(x)->to_xxxx(y) often generates duplicate lines in the trace
                    if ($last === "{$frame['file']}:{$frame['line']}") {
                        continue;
                    }
                }

                $result[] = $last = "{$frame['file']}:{$frame['line']}";
            }
        }

        $this->write(implode("\n", $result) . "\n");
    }
}
