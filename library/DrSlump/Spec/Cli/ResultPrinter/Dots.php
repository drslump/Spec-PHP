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
    const MAX_COLUMN = 50;

    protected $column = 0;

    /**
     * @param \PHPUnit_Framework_Test $test
     * @param float $time
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        parent::endTest($test, $time);

        switch ($this->lastTestResult) {
        case self::FAILED:
            $progress = "\033[31mF\033[0m";
            break;
        case self::ERROR:
            $progress = "\033[31;1;7mE\033[0m";
            break;
        case self::INCOMPLETE:
            $progress = "\033[30mI\033[0m";
            break;
        case self::SKIPPED:
            $progress = "\033[30;1mS\033[0m";
            break;
        case self::PASSED:
            $char = $this->colors ? '+' : '.';
            $progress = "\033[32m$char\033[0m";
            break;
        }

        $this->write($progress);

        $this->column += 1;
        if ($this->column >= self::MAX_COLUMN) {
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
