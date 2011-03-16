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

use DrSlump\Spec;

require_once __DIR__ . '/../Spec.php';

require_once 'PHPUnit/Autoload.php';


/**
 * Implements the command line interface
 *
 * @package     Spec
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
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
            print_r($result);

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

            // By default run the test
            $module = new Cli\Modules\Test($result);
            $module->run();
            exit(0);

            throw new \Exception('Unsupported option');

        } catch (\Exception $exc) {
            $main->displayError($exc->getMessage());
        }
    }


    static public function beep()
    {
        $count = 2;
        $delay = floor(0.25 * 1000000);

        for ($i=0; $i<$count; $i++) {
            echo "\x07";
            usleep($delay);
        }
    }

    static public function growl()
    {

        require_once 'Net/Growl.php';

        // Notification Type definitions
        define('GROWL_NOTIFY_STATUS', 'GROWL_NOTIFY_STATUS');
        define('GROWL_NOTIFY_PHPERROR', 'GROWL_NOTIFY_PHPERROR');

        // define a PHP application that sends notifications to Growl

        $appName = 'PHP App Example using UDP';
        $notifications = array(
            GROWL_NOTIFY_STATUS => array(),
            GROWL_NOTIFY_PHPERROR => array()
        );

        $password = 'foobar';
        $options  = array('host' => 'localhost');

        try {
            $growl = \Net_Growl::singleton($appName, $notifications, $password, $options);
            $growl->register();

            $name        = GROWL_NOTIFY_STATUS;
            $title       = 'Congratulation';
            $description = 'Congratulation! You are successfull install PHP/NetGrowl.';
            $growl->notify($name, $title, $description);

            $name        = GROWL_NOTIFY_PHPERROR;
            $title       = 'PHP Error';
            $description = 'You have a new PHP error in your script P at line N';
            $options     = array(
                'sticky'   => true,
                'priority' => \Net_Growl::PRIORITY_HIGH,
            );
            $growl->notify($name, $title, $description, $options);

            $name        = GROWL_NOTIFY_STATUS;
            $title       = 'Welcome';
            $description = "Welcome in PHP/Growl world ! \n"
                         . "Old UDP protocol did not support icons.";
            $growl->notify($name, $title, $description);

            var_export($growl);

        } catch (\Net_Growl_Exception $e) {
            echo 'Caught Growl exception: ' . $e->getMessage() . PHP_EOL;
        }


        exit;

        // @todo Is GNTP supported by OSX's original Growl?

        require_once 'Net/Growl.php';

        // Notification Type definitions
        define('GROWL_NOTIFY_STATUS', 'GROWL_NOTIFY_STATUS');
        define('GROWL_NOTIFY_PHPERROR', 'GROWL_NOTIFY_PHPERROR');

        // define a PHP application that sends notifications to Growl

        $appName = 'PHP App Example using GNTP';
        $notifications = array(
            GROWL_NOTIFY_STATUS => array(
                'display' => 'Status',
            ),

            GROWL_NOTIFY_PHPERROR => array(
                'icon'    => 'http://www.laurent-laville.org/growl/images/firephp.png',
                'display' => 'Error-Log'
            )
        );

        $password = 'foobar';
        $options  = array(
            'host'     => 'localhost',
            'protocol' => 'tcp', 'port' => \Net_Growl::GNTP_PORT, 'timeout' => 15,
            'AppIcon'  => 'http://www.laurent-laville.org/growl/images/Help.png',
            'debug'    => '/tmp/netgrowl.log'
        );

        try {
            $growl = \Net_Growl::singleton($appName, $notifications, $password, $options);
            $growl->register();

            $name        = GROWL_NOTIFY_STATUS;
            $title       = 'Congratulation';
            $description = 'Congratulation! You are successfull install PHP/NetGrowl.';
            $options     = array();
            $growl->notify($name, $title, $description, $options);

            $name        = GROWL_NOTIFY_PHPERROR;
            $title       = 'PHP Error';
            $description = 'You have a new PHP error in your script P at line N';
            $options     = array(
                'priority' => Net_Growl::PRIORITY_HIGH,
            );
            $growl->notify($name, $title, $description, $options);

            $name        = GROWL_NOTIFY_STATUS;
            $title       = 'Welcome';
            $description = "Welcome in PHP/GNTP world ! \n"
                         . "New GNTP protocol add icon support.";
            $options     = array(
                'icon' => 'http://www.laurent-laville.org/growl/images/unknown.png',
                'sticky' => false,
            );
            $growl->notify($name, $title, $description, $options);

            var_export($growl);

        } catch (\Net_Growl_Exception $e) {
            echo 'Caught Growl exception: ' . $e->getMessage() . PHP_EOL;
        }

    }

    static public function highlight()
    {
        $src = highlight_file(__FILE__, true);
        $src = preg_replace('/<br[^>]*>/', PHP_EOL, $src);
        $src = preg_replace_callback('/<span style="color: #([0-F]+)">/', function($m){
            switch ($m[1]) {
                case '0000BB': // Idents
                    return "\033[34;1m";
                case 'FF8000': // Comments
                    return "\033[30m";
                case '007700': // Symbols
                    return "\033[37;1m";
                case 'DD0000': // Strings
                    return "\033[32;1m";
                default:
                    return "\033[0m";
            }
        }, $src);
        // Ensure no tags are left
        $src = preg_replace('/<[^>]+>/', '', $src);
        $src = str_replace('&nbsp;', ' ', $src);
        $src = html_entity_decode($src);
        $src = $src . "\033[0m";

        $lines = explode(PHP_EOL, $src);
        array_shift($lines);
        $padding = strlen(count($lines));
        // Coloring line numbers break multi line comments
        // Perhaps we should apply colors line by line?
        foreach ($lines as $idx=>&$line) {
            $line = "\033[33m" .
                    str_pad($idx+1, $padding, ' ', STR_PAD_LEFT) .
                    "\033[0m" .
                    ' ' .
                    $line;
        }

        $src = implode(PHP_EOL, $lines) . PHP_EOL;

        echo $src;
    }

}
