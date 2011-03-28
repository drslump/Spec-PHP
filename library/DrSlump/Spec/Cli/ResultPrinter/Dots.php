<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

namespace DrSlump\Spec\Cli\ResultPrinter;

use DrSlump\Spec\Cli;

/**
 * Standard result printer that prints a dot for each test passed
 *
 * @package     Spec\Cli\ResultPrinter
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
 */
class Dots extends Cli\ResultPrinter implements \PHPUnit_Framework_TestListener
{

    /**
     * @param \PHPUnit_Framework_Test $test
     * @param float $time
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        parent::endTest($test, $time);

        switch ($this->lastTestResult) {
        case self::FAILED:
			$ch = $this->colors ? '✘' : 'F';
            $progress = "\033[31m$ch\033[0m";
            break;
        case self::ERROR:
            $ch = $this->colors ? '▪' : 'E';
            $progress = "\033[31m$ch\033[0m";
            break;
        case self::INCOMPLETE:
            $ch = $this->colors ? '▫' : 'I';
            $progress = "\033[30m$ch\033[0m";
            break;
        case self::SKIPPED:
            $ch = $this->colors? '▫' : 'S';
            $progress = "\033[30;1m$ch\033[0m";
            break;
        case self::PASSED:
            $ch = $this->colors ? '•' : '.';
            $progress = "\033[32m$ch\033[0m";
            break;
        default:
            throw new \RuntimeException('Unknown test result "' . $this->lastTestResult . '"');
        }

        $this->write($progress);
        $this->column += 1;
        if ($this->column >= $this->maxColumns-10) {
            $this->column = 0;
            $this->write(PHP_EOL);
        }
    }

    public function flush()
    {
        $this->write(PHP_EOL);
        parent::flush();
    }
}
