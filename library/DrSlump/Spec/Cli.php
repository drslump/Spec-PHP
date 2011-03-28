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
                'short_name'    => '-f',
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

        $main->addOption(
            'lines',
            array(
                'long_name'     => '--show-lines',
                'action'        => 'StoreTrue',
                'description'   => 'include line numbers on dump',
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
            $main->displayError($exc->getMessage());
        }
    }

}
