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

namespace DrSlump\Spec\PHPUnit\ResultPrinter;

use DrSlump\Spec\PHPUnit;

/**
 * Standard result printer that prints a dot for each test passed
 *
 * @package     Spec\PHPUnit\ResultPrinter
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class Dots extends PHPUnit\ResultPrinter implements \PHPUnit_Framework_TestListener
{
    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        parent::addError($test, $e, $time);
        $this->progress('E');
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
        parent::addFailure($test, $e, $time);
        $this->progress('F');
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
        parent::addIncompleteTest($test, $e, $time);
        $this->progress('I');
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        parent::addSkippedTest($test, $e, $time);
        $this->progress('S');
    }

    /**
     * @param \PHPUnit_Framework_Test $test
     * @param  $time
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        parent::endTest($test, $time);
        $this->progress('.');
    }

    /**
     * Writes progress
     *
     * @param string $progress
     */
    protected function progress($progress)
    {
        if ($this->colors) {
            switch ($progress) {
                case 'F':   // Red
                    $progress = "\033[31mF\033[0m";
                    break;
                case 'E':   // Red Bg
                    $progress = "\033[31;1;5;7mE\033[0m";
                    break;
                case 'I':   // Yellow
                    $progress = "\033[30;47mI\033[0m";
                    break;
                case 'S':   // Gray
                    $progress = "\033[30;47;7mS\033[0m";
                    break;
                case '.':   // White
                    $progress = "\033[32;1m$progress\033[0m";
                    break;
            }
        }

        $this->write($progress);
    }
}
