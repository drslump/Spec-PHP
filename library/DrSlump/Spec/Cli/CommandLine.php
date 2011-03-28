<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

namespace DrSlump\Spec\Cli;


// Include PEAR's Console_CommandLine
require_once 'Console/CommandLine.php';


class CommandLine extends \Console_CommandLine
{
    protected function getArgcArgv()
    {
        if (empty($this->commands)) {
            return parent::getArgcArgv();
        }

        list($argc, $argv) = parent::getArgcArgv();

        // Obtain the list of options
        $opts = array();
        foreach ($this->options as $opt) {
            $opts[] = $opt->short_name;
            $opts[] = $opt->long_name;
        }

        // Get commands
        $commands = array();
        foreach ($this->commands as $cmd) {
            $commands[] = $cmd->name;
            $commands = array_merge($commands, $cmd->aliases);
        }

        // Inject default command if none given
        $commands = array_keys($this->commands);
        for ($i=1; $i<$argc; $i++) {
            if (!in_array($argv[$i], $opts) && !in_array($argv[$i], $commands)) {
                array_splice($argv, $i, 0, $commands[0]);
                $argc++;
                break;
            }
        }

        return array($argc, $argv);
    }

}
