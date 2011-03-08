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
class ResultPrinter extends \PHPUnit_TextUI_ResultPrinter
{

    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        parent::startTestSuite($suite);

        if ($this->verbose && $suite instanceof TestSuite) {
            // Skip root suite
            if (!$suite->getParent()) return;

            $levels = 0;
            if ($parent = $suite->getParent()) {
                while($parent = $parent->getParent()) { $levels++; }
            }

            $output = str_repeat("  ", $levels);
            $output.= $suite->getTitle();
            if ($this->colors) {
                $output = "\033[34;1m" . $output . "\033[0m";
            }

            $this->write("\n" . $output . "\n");
        }
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {

        if ($this->verbose && $test instanceof TestCase) {
            $levels = 0;
            if ($parent = $test->getParent()) {
                while($parent = $parent->getParent()) { $levels++; }
            }

            $output = str_repeat("  ", $levels) . "it " . $test->getTitle();
            if ($this->lastTestFailed) {
                $output = substr($output, 1);
                if ($this->colors) {
                    $output = "\033[37;41;1mF\033[0m\033[31;1m$output\033[0m";
                } else {
                    $output = 'F' . $output;
                }
            } else {
                if ($this->colors) {
                    $output = "\033[32;1m" . $output . "\033[0m";
                }
            }

            $this->write($output . "\n");
        }

        parent::endTest($test, $time);
    }


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

    // Improved progress indicator with color output
    protected function writeProgress($progress)
    {
        // Do not print progress if in verbose mode
        if ($this->verbose) return;

        if ($this->colors) {
            switch ($progress) {
                case 'F':   // Red
                    $progress = "\033[31m$progress\033[0m";
                    break;
                case 'E':   // Red Bg
                    $progress = "\033[37;41m$progress\033[0m";
                    break;
                case 'I':   // Yellow
                    $progress = "\033[33;1m$progress\033[0m";
                    break;
                case 'S':   // Gray
                    $progress = "\033[37m$progress\033[0m";
                case '.':   // White
                    $progress = "\033[32;1m$progress\033[0m";
                    break;
            }
        }

        parent::writeProgress($progress);
    }
}
