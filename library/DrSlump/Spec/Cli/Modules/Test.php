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

namespace DrSlump\Spec\Cli\Modules;

use DrSlump\Spec;

/**
 * Runs spec files
 *
 * @package     Spec\Cli
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class Test
{
    /** Default expasion pattern to search for spec files */
    const DEFAULT_GLOB = '*{Spec,.spec,_spec}.php';

    /** @var \Console_CommandLine_Result */
    protected $result;

    /**
     * @param \Console_CommandLine_Result $result
     */
    public function __construct(\Console_CommandLine_Result $result)
    {
        $this->result = $result;
    }

    /**
     * Search for files in a directory matching a pattern
     *
     * @param \DrSlump\Spec\TestSuite $suite
     * @param string $dir
     * @param string $glob  Unix style expansion pattern
     * @return void
     */
    protected function searchForSpecs(Spec\TestSuite $suite, $dir, $glob = self::DEFAULT_GLOB)
    {
        // Convert linux style wildcards to a regular expression
        $glob =
        str_replace(
            array('\.', '\?', '\*', '\[!', '\{', '\}', ','),
            array('\.', '.',  '.*', '[^',  '(', ')', '|'),
            preg_quote($glob)
        );

        $glob = "/^$glob$/";

        // Windows and OSX (Darwin) systems compare without case
        if (FALSE !== stripos(PHP_OS, 'WIN')) {
            $glob .= 'i';
        }

        // Linux-like systems should provide already expanded arguments,
        // in any case this will try to emulate that expansion.
        $it = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS);
        $it = new \RecursiveIteratorIterator($it);
        foreach ($it as $file) {
            if (preg_match($glob, $file->getFilename())) {
                $suite->addTestFile($file->getPathname());
            }
        }
    }

    /**
     * Runs this module
     *
     * @throws \Exception
     */
    public function run()
    {
        // Create a suite to add spec files
        $suite = new Spec\TestSuite();

        // For every argument given check what files it matches
        foreach ($this->result->args['files'] as $file) {
            if (is_file($file)) {
                $suite->addTestFile($file);
            } else if (is_dir($file)) {
                $this->searchForSpecs($suite, $file);
            } else {
                $glob = basename($file);
                $file = dirname($file);
                $this->searchForSpecs($suite, $file, $glob);
            }
        }

        // Create a printer instance

        if ($this->result->options['story']) {
            $this->result->options['format'] = 'story';
        }

        // @todo Allow custom class names
        switch (strtolower($this->result->options['format'])) {
            case 'd':
            case 'dots':
                $formatter = '\DrSlump\Spec\Cli\ResultPrinter\Dots';
                break;
            case 's':
            case 'story':
                $formatter = '\DrSlump\Spec\Cli\ResultPrinter\Story';
                break;
            default:
                throw new \RuntimeException('Unknown format option');
        }

        $printer = new $formatter(
            NULL,
            (bool)$this->result->options['verbose'],
            (bool)$this->result->options['color'],
            (bool)$this->result->options['debug']
        );



        // Create a PHPUnit result manager
        $result = new \PHPUnit_Framework_TestResult();
        // Append our custom printer as a listener
        $result->addListener($printer);

        // Register beeping listener
        if ($this->result->options['beep']) {
            $result->addListener(
                new Spec\Cli\BeepListener()
            );
        }

        // Run the suite
        $suite->run(
          $result,
          false, //$arguments['filter'],
          array(), //$arguments['groups'],
          array(), //$arguments['excludeGroups'],
          false  //$arguments['processIsolation']
        );

        unset($suite);
        $result->flushListeners();

        $printer->printResult($result);
    }
}
