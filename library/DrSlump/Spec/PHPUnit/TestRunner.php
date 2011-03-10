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
 * Extends a PHPUnit Test Runner to adapt it for Spec
 *
 * @package     Spec\PHPUnit
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class TestRunner extends \PHPUnit_TextUI_TestRunner
{

    /**
     * We need to override this method to ensure it fetches XxxxSpec.php files
     *
     * @param  string  $suiteClassName
     * @param  string  $suiteClassFile
     * @param  boolean $syntaxCheck
     * @return \PHPUnit_Framework_Test
     */
    public function getTest($suiteClassName, $suiteClassFile = '', $syntaxCheck = FALSE)
    {
        if (is_dir($suiteClassName) &&
            !is_file($suiteClassName . '.php') && empty($suiteClassFile)) {

            $testCollector = new \PHPUnit_Runner_IncludePathTestCollector(
                array($suiteClassName),
                array('Spec.php', '.spec.php', 'Test.php', '.phpt')
            );

            $suite = new TestSuite($suiteClassName);
            $suite->addTestFiles(
                $testCollector->collectTests(),
                $syntaxCheck
            );

            return $suite;
        }

        // Handle single file

        $fname = $suiteClassFile;
        if (empty($fname)) {
            $fname = $suiteClassName . '.php';
            if (!is_file($fname)) {
                $fname = $suiteClassName . '.spec.php';
            }
        }

        if (is_file($fname)) {
            // Try to detect if it's a spec
            $src = file_get_contents($fname);
            if (preg_match('/\bdescribe\b/', $src) &&
                preg_match('/\bshould\b/', $src) &&
                preg_match('/\bit\b/', $src)) {

                $suite = new TestSuite(basename($fname));
                $suite->addTestFile($fname, FALSE);
                return $suite;
            }
        }


        // Fallback to default behaviour
        return parent::getTest($suiteClassName, $suiteClassFile, $syntaxCheck);
    }

}
