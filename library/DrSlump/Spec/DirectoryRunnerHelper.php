<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

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
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
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
            if (preg_match('/(\.spec|_spec|Spec)\.php$/i', $file->getFilename())) {
                $suite->addTestFile($file->getPathname());
            }
        }

        return $suite;
    }

}
