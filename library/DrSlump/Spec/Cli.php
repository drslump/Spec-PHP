<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

namespace DrSlump\Spec;

use DrSlump\Spec;

require_once __DIR__ . '/../Spec.php';

// Setup PHPUnit's autoloader
require_once 'PHPUnit/Autoload.php';


/**
 * Implements the command line interface
 *
 * @package     Spec
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
 */
class Cli
{
    static function run()
    {
        $main = new Cli\CommandLine(array(
            'description' => 'Spec for PHP ' . Spec::VERSION . ' by Ivan -DrSlump- Montes',
            'version'     => Spec::VERSION,
        ));

        $main->addOption(
            'color',
            array(
                'long_name'   => '--color',
                'action'      => 'StoreString',
                'description' => 'turn on colored output [yes, no, *auto]',
                'choices'     => array( 'auto', 'yes', 'no' ),
                'default'     => 'auto',
            )
        );

        $main->addOption(
            'verbose',
            array(
                'short_name'  => '-v',
                'long_name'   => '--verbose',
                'action'      => 'StoreTrue',
                'description' => 'turn on verbose output'
            )
        );

        $main->addOption(
            'debug',
            array(
                'long_name'   => '--debug',
                'action'      => 'StoreTrue',
                'description' => 'turn on debug output'
            )
        );

        $main->addOption(
            'filter',
            array(
                'multiple'      => true,
                'short_name'    => '-f',
                'long_name'     => '--filter',
                'action'        => 'StoreArray',
                'help_name'     => 'regexp',
                'description'   => 'filter which tests to run (regexp)'
            )
        );

        $main->addOption(
            'groups',
            array(
                'multiple'      => true,
                'short_name'    => '-g',
                'long_name'     => '--group',
                'action'        => 'StoreArray',
                'help_name'     => 'group',
                'description'   => 'run only this group (csv)'
            )
        );

        $main->addOption(
            'exclude_groups',
            array(
                'multiple'      => true,
                'long_name'     => '--exclude-group',
                'action'        => 'StoreArray',
                'help_name'     => 'group',
                'description'   => 'do not run this group (csv)'
            )
        );

        $main->addOption(
            'list_groups',
            array(
                'long_name'     => '--list-groups',
                'action'        => 'StoreTrue',
                'description'   => 'show available groups'
            )
        );

        $main->addOption(
            'story',
            array(
                'short_name'    => '-s',
                'action'        => 'StoreTrue',
                'description'   => 'turn on story style formatting'
            )
        );

        $main->addOption(
            'format',
            array(
                'long_name'     => '--format',
                'action'        => 'StoreString',
                'default'       => 'dots',
                'description'   => 'output format [*dots, story]'
            )
        );

        $main->addOption(
            'beep',
            array(
                'short_name'    => '-b',
                'long_name'     => '--beep',
                'action'        => 'StoreTrue',
                'description'   => 'turn on beep on failure',
            )
        );

        $main->addOption(
            'dump',
            array(
                'short_name'    => '-d',
                'long_name'     => '--dump',
                'action'        => 'StoreTrue',
                'description'   => 'dump a spec file transformed to PHP',
            )
        );

        $main->addArgument(
            'files',
            array(
                'multiple'      => true,
                'description'   => 'spec files'
            )
        );

        // run the parser
        try {
            $result = $main->parse();

            // Check if we can use colors
            if ($result->options['color'] === 'auto') {
                $result->options['color'] = DIRECTORY_SEPARATOR != '\\' && function_exists('posix_isatty') && @posix_isatty(STDOUT);
            } else {
                $result->options['color'] = filter_var($result->options['color'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }

            if ($result->options['dump']) {
                $module = new Cli\Modules\Dump($result);
                $module->run();
                exit(0);
            }

            echo $main->description . PHP_EOL . PHP_EOL;

            // By default run the test
            $module = new Cli\Modules\Test($result);
            $module->run();
            exit(0);

            throw new \Exception('Unsupported option');

        } catch (\Exception $exc) {
            $msg = $exc->getMessage();
            if ($result && ($result->options['verbose'] || $result->options['debug'])) {
                $msg .= PHP_EOL . $exc->getTraceAsString();
                while ($exc->getPrevious()) {
                    $exc = $exc->getPrevious();
                    $msg .= PHP_EOL . $exc->getMessage();
                    $msg .= PHP_EOL . $exc->getTraceAsString();
                }
            }

            $main->displayError($msg);
        }
    }
}
