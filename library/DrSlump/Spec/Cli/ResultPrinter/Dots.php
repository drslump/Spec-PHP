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
    /**
     * @param \PHPUnit_Framework_Test $test
     * @param float $time
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        parent::endTest($test, $time);

        switch ($this->lastTestResult) {
        case self::FAILED:
            $progress = $this->colors ? "\033[31mF\033[0m" : 'F';
            break;
        case self::ERROR:
            $progress = $this->colors ? "\033[31;1;5;7mE\033[0m" : 'E';
            break;
        case self::INCOMPLETE:
            $progress = $this->colors ? "\033[30;47mI\033[0m" : 'I';
            break;
        case self::SKIPPED:
            $progress = $this->colors ? "\033[30;47;7mS\033[0m" : 'S';
            break;
        case self::PASSED:
            $progress = $this->colors ? "\033[32;1m.\033[0m" : '.';
            break;
        }

        $this->write($progress);
    }

    public function flush()
    {
        $this->write(PHP_EOL);
        parent::flush();
    }
}
