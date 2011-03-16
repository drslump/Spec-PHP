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

namespace DrSlump\Spec;

/**
 * Helper class which when inherited will run all the tests found (recursively)
 * in the directory.
 *
 * @package     Spec
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class DirectoryRunnerHelper
{
    static public function suite()
    {
        $class = get_called_class();
        if (__CLASS__ === $class) {
            throw new \RuntimeException(
                "You must override the suite method in your class. ie:\n" .
                "  class AllTests extends \DrSlump\Spec\DirectoryRunnerHelper {\n" .
                "    static public function suite() {\n" .
                "      return parent::suite();\n" .
                "    }\n" .
                "  }\n"
            );
        }

        // Create a root suite
        $suite = new \DrSlump\Spec\TestSuite();

        // Find out the directory where the child class is located
        $reflClass = new \ReflectionClass($class);
        $fname = $reflClass->getFileName();
        $dname = dirname($fname);

        // Recursively find spec files
        $it = new \RecursiveDirectoryIterator($dname, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS);
        $it = new \RecursiveIteratorIterator($it);
        foreach ($it as $file) {
            if (preg_match('/\.spec\.php|Spec\.php/i', $file->getFilename())) {
                $suite->addTestFile($file->getPathname());
            }
        }

        return $suite;
    }

}
