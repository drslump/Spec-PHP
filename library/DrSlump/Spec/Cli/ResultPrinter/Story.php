<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

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
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
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
