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
 * Extends the PHPUnit TextUI Command to adapt it for Spec
 *
 * @todo Deprecate this in favour of a custom command line tool
 *
 * @package     Spec\PHPUnit
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class Command extends \PHPUnit_TextUI_Command
{
    protected $isSpecDump = false;

    public function __construct()
    {
        // We need at least PHPUnit version 3.5.13 since it's the first version
        // to have a createRunner() factory method, which we need to greatly
        // simplify the Spec Runner implementation.
        $version = \PHPUnit_Runner_Version::id();
        if ($version !== '@package_version@' && version_compare($version, '3.5.12', '<')) {
            \PHPUnit_TextUI_TestRunner::showError("PHPUnit version $version is not compatible with Spec extension. Please use version 3.5.13 or newer");
        }

        $this->longOptions['spec'] = 'handleSpec';
        $this->longOptions['spec-version'] = 'handleSpecVersion';
        $this->longOptions['spec-dump='] = 'handleSpecDump';
    }

    /**
 	 * Create a TestRunner, override in subclasses.
 	 *
     * @since 3.5.13
 	 * @return TestRunner
 	 */
    protected function createRunner()
    {
        return new TestRunner($this->arguments['loader']);
    }

    /**
     * @param boolean $exit
     */
    public static function main($exit = TRUE)
    {
        $command = new self;
        $command->run($_SERVER['argv'], $exit);
    }

    /**
     * Show the help message.
     */
    protected function showHelp()
    {
        parent::showHelp();

        $version = TestRunner::VERSION;

        echo "--------------------------------------\n";

        echo "Spec extension "  . TestRunner::VERSION . " by Ivan -DrSlump- Montes\n";

        echo <<<EOT

  --spec                    Enables specs optimized results printer
  --spec-version            Show version information about the Spec extension
  --spec-dump <file>        Parses the given spec file and outputs the resulting
                            PHP code to STDOUT. This is useful to find out if the
                            automatic spec conversion is broken for a file.

EOT;
    }


    protected function handleSpecVersion($value)
    {
        echo "Spec extension "  . TestRunner::VERSION . " by Ivan -DrSlump- Montes\n";
        exit(\PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
    }

    protected function handleSpecDump($fname)
    {
        if (empty($fname)) {
            \PHPUnit_TextUI_TestRunner::showError("No spec file given to dump");
        }

        if (!is_readable($fname)) {
            \PHPUnit_TextUI_TestRunner::showError('Unable to read file: ' . $fname);
        }

        if (!in_array(TestRunner::SCHEME, stream_get_wrappers())) {
            stream_wrapper_register(
                TestRunner::SCHEME,
                'DrSlump\\Spec\\StreamWrapper'
            );
        }

        $content = file_get_contents(TestRunner::SCHEME . '://' . $fname);
        if ($content === false) {
            \PHPUnit_TextUI_TestRunner::showError('Error processing spec file: ' . $fname);
        }

        if (!empty($this->arguments['debug'])) {
            $lines = explode("\n", $content);
            $pad = strlen(count($lines));
            foreach ($lines as $idx=>$line) {
                echo str_pad($idx+1, $pad, ' ', STR_PAD_LEFT) . ' ';
                echo $line . PHP_EOL;
            }
        } else {
            echo $content;
        }

        exit(\PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
    }

    protected function handleSpec($value)
    {
        // Automatically detect if colors are "supported"
        if (empty($this->arguments['colors'])
            && function_exists('posix_isatty')
            && posix_isatty(STDOUT)) {
            $this->arguments['colors'] = true;
        }

        $this->arguments['printer'] = new ResultPrinter(
                  NULL,
                  isset($this->arguments['verbose']) ? $this->arguments['verbose'] : false,
                  isset($this->arguments['colors']) ? $this->arguments['colors'] : false,
                  isset($this->arguments['debug']) ? $this->arguments['debug'] : false
        );
    }
}
