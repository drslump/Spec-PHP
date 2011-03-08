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
 * Extend this class on a file in your tests directory to have PHPUnit
 * include recursively all spec files in the current directory.
 *
 * @package     Spec\PHPUnit
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class DirectorySpecTests
{
    /**
     * @static
     * @param string $name
     * @return TestSuite
     */
    public static function suite($name = null)
    {
        $suite = new TestSuite();
        $suite->setTitle($name);

        $dir = new \RecursiveDirectoryIterator('./');
        $iter = new \RecursiveIteratorIterator($dir);
        $specs = new \RegexIterator($iter, '/^.+\.spec\.php$/i', \RegexIterator::GET_MATCH);
        foreach ($specs as $file) {
            $suite->addTestFile($file[0]);
        }

        return $suite;
    }
}
