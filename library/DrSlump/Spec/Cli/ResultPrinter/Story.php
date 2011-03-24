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

namespace DrSlump\Spec\Cli\ResultPrinter;

use DrSlump\Spec;
use DrSlump\Spec\Cli;

/**
 * Story result printer that prints the titles of each test passed
 *
 * @package     Spec\PHPUnit\ResultPrinter
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class Story extends Cli\ResultPrinter implements \PHPUnit_Framework_TestListener
{
    /**
     * @param \PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        static $first = true;

        parent::startTestSuite($suite);

        if ($suite instanceof Spec\TestSuite) {
            // Skip root suite
            if (!$suite->getParent()) return;

            $levels = 0;
            if ($parent = $suite->getParent()) {
                while($parent = $parent->getParent()) { $levels++; }
            }

            $output = "\033[37;4m" . $suite->getTitle() . "\033[0m";
            $output = str_repeat("  ", $levels) . $output;

            if ($this->verbose && !$suite->getParent()->getParent()) {
                $filename = "\033[30;1m[" . basename($suite->getFilename()) . "]\033[0m";
                $output .= ' ' . $filename;
            }

            if ($first) {
                $first = false;
            } else {
                $this->write(PHP_EOL);
            }

            $this->write($output . PHP_EOL);
        }
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        if ($test instanceof Spec\TestCaseInterface) {
            $levels = 0;
            if ($parent = $test->getSuite()) {
                while($parent = $parent->getParent()) { $levels++; }
            }

            $output = str_repeat("  ", $levels) . $test->getTitle();
            if ($this->lastTestResult !== self::PASSED) {

                switch ($this->lastTestResult) {
                case self::FAILED:
                    $output.= ' (FAILED - ' . count($this->exceptions) . ')';
                    $output = "\033[31m$output\033[0m";
                    break;
                case self::ERROR:
                    $output.= ' (ERROR - ' . count($this->exceptions) . ')';
                    $output = "\033[31m$output\033[0m";
                    break;
                case self::INCOMPLETE:
                    $output.= ' (INCOMPLETE)';
                    $output = "\033[30;1m$output\033[0m";
                    break;
                case self::SKIPPED:
                    $output.= ' (SKIPPED)';
                    $output = "\033[30;1m$output\033[0m";
                    break;
                }
            } else {
                $output = "\033[32m" . $output . "\033[0m";
            }

            $this->write($output . PHP_EOL);
        }
    }
}
