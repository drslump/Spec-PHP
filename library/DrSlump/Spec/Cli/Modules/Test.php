<?php

namespace DrSlump\Spec\Cli\Modules;

use DrSlump\Spec;

class Test
{
    const DEFAULT_GLOB = '*{Spec|.spec}.php';

    /** @var Console_CommandLine_Result */
    protected $result;

    public function __construct(\Console_CommandLine_Result $result)
    {
        $this->result = $result;
    }

    protected function searchForSpecs(Spec\PHPUnit\TestSuite $suite, $dir, $glob = self::DEFAULT_GLOB)
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

    public function run()
    {
        // Create a suite to add spec files
        $suite = new Spec\PHPUnit\TestSuite();

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
        switch (strtolower($this->result->options['format'])) {
            case 'd':
            case 'dots':
                $formatter = '\DrSlump\Spec\PHPUnit\ResultPrinter\Dots';
                break;
            case 's':
            case 'story':
                $formatter = '\DrSlump\Spec\PHPUnit\ResultPrinter\Story';
                break;
            default:
                if (!$this->result->options['story']) {
                    throw new \Exception('Unknown format option');
                }
                $formatter = '\DrSlump\Spec\PHPUnit\ResultPrinter\Story';
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
    }




    static public function command()
    {
        require_once 'Console/CommandLine/Command.php';

        $cmd = new \Console_CommandLine_Command(array(
            'name'          => 'test',
            'description'   => 'Runs the spec files',
        ));

        $cmd->addArgument('files',
            array(
                'multiple'      => true,
                'description'   => 'list of spec files or a directory'
        ));

        return $cmd;
    }

}
