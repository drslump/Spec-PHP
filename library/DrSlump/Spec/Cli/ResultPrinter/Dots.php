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

use DrSlump\Spec\Cli;

/**
 * Standard result printer that prints a dot for each test passed
 *
 * @package     Spec\Cli\ResultPrinter
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class Dots extends Cli\ResultPrinter implements \PHPUnit_Framework_TestListener
{
    const MAX_COLUMNS = 60;

    protected $maxColumns = self::MAX_COLUMNS;
    protected $column = 0;

    public function __construct($out = NULL, $verbose = FALSE, $colors = FALSE, $debug = FALSE)
    {
        parent::__construct($out, $verbose, $colors);

        if (!empty($_SERVER['COLUMNS'])) {
            $this->maxColumns = $_SERVER['COLUMNS'] - 10;
        }
    }

    /**
     * @param \PHPUnit_Framework_Test $test
     * @param float $time
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        parent::endTest($test, $time);

        switch ($this->lastTestResult) {
        case self::FAILED:
			$ch = $this->colors ? '✗ ' : 'F';
            $progress = "\033[31m$ch\033[0m";
            break;
        case self::ERROR:
            $ch = $this->colors ? '✖ ' : 'E';
            $progress = "\033[31m$ch\033[0m";
            break;
        case self::INCOMPLETE:
            $ch = $this->colors ? '⟐ ' : 'I';
            $progress = "\033[30m$ch\033[0m";
            break;
        case self::SKIPPED:
            $ch = $this->colors? '⟐ ' : 'S';
            $progress = "\033[30;1m$ch\033[0m";
            break;
        case self::PASSED:
            $ch = $this->colors ? '✓ ' : '.';
            $progress = "\033[32m$ch\033[0m";
            break;
        }

        $this->write($progress);

        $this->column += $this->colors ? 2 : 1;
        if ($this->column >= $this->maxColumns) {
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
