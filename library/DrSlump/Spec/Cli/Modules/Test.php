<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

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
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
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
