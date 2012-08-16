<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

namespace DrSlump\Spec\Cli;

use DrSlump\Spec;
use DrSlump\Spec\Coverage\Filter;

/**
 * Extends a PHPUnit Result Printer to adapt it for Spec
 *
 * @package     Spec\Cli
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
 */
class ResultPrinter extends \PHPUnit_TextUI_ResultPrinter implements \PHPUnit_Framework_TestListener
{
    const PASSED        = 1;
    const FAILED        = 2;
    const ERROR         = 3;
    const INCOMPLETE    = 4;
    const SKIPPED       = 5;

    const MAX_COLUMNS = 79;

    /** @var int */
    protected $lastTestResult;
    protected $exceptions = array();
    protected $failures = array();
    protected $errors = array();
    protected $incomplete = array();
    protected $skipped = array();

    protected $maxColumns = self::MAX_COLUMNS;
    protected $column = 0;

    public function __construct($out = NULL, $verbose = FALSE, $colors = FALSE, $debug = FALSE)
    {
        parent::__construct($out, $verbose, $colors);

        if (!empty($_SERVER['COLUMNS'])) {
            $this->maxColumns = $_SERVER['COLUMNS'];
        } else if (function_exists('ncurses_getmaxyx')) {
            ncurses_getmaxyx(STDSCR, $height, $this->maxColumns);
        } else {
            // Try to get it on *nix like systems
            exec('resize 2>/dev/null', $output, $ret);
            if ($ret === 0 && preg_match('/COLUMNS=([0-9]+)/', $output[0], $m)) {
                $this->maxColumns = $m[1];
            }
        }
    }

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
        $this->exceptions[] = array($test, $e);
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
        $this->exceptions[] = array($test, $e);
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
     * Override this method to automatically remove ansi codes
     */
    public function write($data)
    {
        if (!$this->colors) {
            $data = preg_replace("/\x1b\[[^A-Za-z]*[A-Za-z]/", '', $data);
        }
        parent::write($data);
    }

    public function printResult(\PHPUnit_Framework_TestResult $result)
    {
        $upLine = str_repeat($this->colors ? '▁' : '-', $this->maxColumns);
        $dnLine = str_repeat($this->colors ? '▔' : '-', $this->maxColumns);
        $arrow = $this->colors ? '❯' : '=>';

        $this->printFailures();
        if (!count($this->exceptions)) {
            $ch = $this->colors ? ' ✔' : '';
            $upLine = "\033[32m" . $upLine . "\n\033[0m";
            $dnLine = "\033[32m" . $dnLine . "\n\033[0m";
            $str = "\x1b[32m$ch\x1b[0m OK $arrow Passed %s of %s";
            $str = sprintf($str, count($result->passed()), $result->count());
        } else {
            $ch = $this->colors ? ' ✖' : '';
            $upLine = "\033[31m" . $upLine . "\n\033[0m";
            $dnLine = "\033[31m" . $dnLine . "\n\033[0m";
            $str = "\x1b[31m$ch\x1b[0m KO $arrow Failed %s of %s";
            $str = sprintf($str, $result->failureCount()+$result->errorCount(), $result->count());
        }

        if (!$result->allCompletlyImplemented()) {
            $pair = array();
            if ($result->notImplementedCount() > 0) {
                $pair[] = $result->notImplementedCount() . ' not implemented';
            }
            if ($result->skippedCount() > 0) {
                $pair[] = $result->skippedCount() . ' skipped';
            }
            $str .= " with " . implode(' and ', $pair);
        }

        // Calculate time and peak memory usage
        $time = number_format($result->time(), 2);
        $mem = memory_get_peak_usage();
        $mem = round($mem/1024/1024);

        // Add time spent
        $str.= " \033[30;1m({$time}s {$mem}Mb)\n\033[0m";

        // Clean up the line above and print there
        $this->write("\033[1A\033[2K");
        $this->write($upLine);
        $this->write($str);
        $this->write($dnLine);
    }

    /**
     * Prints the failures and errors found so far
     *
     */
    public function printFailures()
    {
        $this->write(PHP_EOL);
        if (count($this->exceptions)) {
			$ch = $this->colors ? '✖' : '=>';
            $suffix = $this->colors ? str_repeat('▬', $this->maxColumns-12) : '';
            $this->write("$ch Failures $suffix\033[0m" . PHP_EOL . PHP_EOL);

            foreach($this->exceptions as $idx => $pair) {
                list($test, $ex) = $pair;

                $title = $test->getSuite()->getTitle() . ': ' . $test->getTitle();
                $this->printException($idx+1, $title, $ex);
            }
        }
    }

    protected function printException($idx, $title, \Exception $ex)
    {
        $indent = str_repeat(' ', strlen("  $idx) "));

        if ($ex instanceof \PHPUnit_Framework_SyntheticError) {
            $trace = $ex->getSyntheticTrace();
        } else {
            $trace = $ex->getTrace();
        }

        // Insert exception as first element of the trace
        array_unshift($trace, array(
            'file'  => $ex->getFile(),
            'line'  => $ex->getLine(),
        ));

        // Process and filter stack trace
        $last = null;
        $offending = null;
        $stacktrace = array();
        $groups = $this->debug ? array() : array('DEFAULT', 'PHPUNIT');
        $filter = Filter::getInstance();
        foreach ($trace as $frame) {
            if (isset($frame['file']) && isset($frame['line']) &&
                !$filter->isFiltered($frame['file'], $groups, TRUE)) {

                // Skip duplicated frames
                if (!$this->debug && $last && $last['file'] === $frame['file'] && $last['line'] === $frame['line']) {
                    continue;
                }

                $last = $frame;

                // Skip blacklisted eval frames: /path/to/file(line) : eval()'d code:line
                if (preg_match('/^(.+?)\([0-9]+\)\s:\seval/', $frame['file'], $m)) {
                    if ($filter->isFiltered($m[1], $groups, TRUE)) {
                        continue;
                    }
                }

                // Check spec files
                if (0 === strpos($frame['file'], Spec::SCHEME . '://')) {
                    $frame['file'] = substr($frame['file'], strlen(Spec::SCHEME . '://'));
                    if (0 !== strpos($frame['file'], '/')) {
                        $frame['file'] = '.' . DIRECTORY_SEPARATOR . $frame['file'];
                    }

                    $lines = file($frame['file']);
                    $offending = trim($lines[ $frame['line']-1 ]);
                }

                $stacktrace[] = $frame['file'] . ':' . $frame['line'];
            }
        }

        // Print title
        $this->write("  $idx) $title" . PHP_EOL);
        // Print exception message
        $msg = str_replace(PHP_EOL, PHP_EOL . $indent, $ex->getMessage());
        $this->write($indent . "\033[31m$msg\033[0m" . PHP_EOL);

        // Print offending spec line if found
        if ($offending) {
            $ch = $this->colors ? '❯' : '>';
            $this->write($indent . "\033[33;1m$ch $offending\033[0m" . PHP_EOL);
        }

        $ch = '#';
        foreach ($stacktrace as $frame) {
            $this->write("$indent\033[30;1m$ch $frame\033[0m" . PHP_EOL);

            if (!$this->verbose && !$this->debug) {
                break;
            }
        }

        $this->write(PHP_EOL);
    }
}
