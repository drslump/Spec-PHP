<?php

namespace DrSlump\Spec\Cli\Modules;

use DrSlump\Spec;

class Dump
{
    /** @var Console_CommandLine_Result */
    protected $result;

    public function __construct(\Console_CommandLine_Result $result)
    {
        $this->result = $result;
    }

    public function run()
    {
        if (empty($this->result->args['files'])) {
            throw new \Exception('No file given');
        } else if (1 < count($this->result->args['files'])) {
            throw new \Exception('Only one file can be given');
        }

        $fname = $this->result->args['files'][0];
        if (file_exists($fname) && !is_readable($fname)) {
            throw new \Exception('File not exists or is not readable');
        }

        $data = file_get_contents($fname);
        $data = Spec\Parser::parse($data);

        if ($this->result->options['color']) {
            $data = $this->highlight($data);
        }

        if ($this->result->options['lines']) {
            $data = $this->linenumbers($data);
        }

        echo $data . PHP_EOL;
    }


    public function highlight($data)
    {
        $src = highlight_string($data, true);

        $src = str_replace("\n", '', $src);
        $src = preg_replace('/<br[^>]*>/', PHP_EOL, $src);
        $src = preg_replace_callback(
            '/<span style="color: #([0-F]+)">/',
            function($m){
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
            },
            $src
        );

        // Ensure no tags are left
        $src = preg_replace('/<[^>]+>/', '', $src);
        $src = str_replace('&nbsp;', ' ', $src);
        $src = html_entity_decode($src);
        $src = $src . "\033[0m";

        return $src;
    }

    public function linenumbers($src)
    {
        $lines = explode(PHP_EOL, $src);
        $padding = strlen(count($lines));
        // Coloring line numbers break multi line comments so we have to
        // keep track of the last color code used
        $lastColor = "\033[0m";
        foreach ($lines as $idx=>&$line) {
            if (($pos = strrpos($line, "\033[")) !== false) {
                preg_match("/(\x1B\[[^m]+m)/", $line, $m, null, $pos);
                $lastColor = $m[1];
            }

            $line = "\033[33m" .
                    str_pad($idx+1, $padding, ' ', STR_PAD_LEFT) .
                    $lastColor .
                    ' ' .
                    $line;
        }

        $src = implode(PHP_EOL, $lines);

        if (!$this->result->options['color']) {
            $src = preg_replace("/\x1B\[[^m]+m/", '', $src);
        }

        return $src;
    }



    static public function command()
    {
        require_once 'Console/CommandLine/Command.php';

        $cmd = new \Console_CommandLine_Command(array(
            'name'          => 'dump',
            'description'   => 'Shows the generated source code',
        ));

        $cmd->addOption('lines', array(
            'short_name'    => '-l',
            'long_name'     => '--lines',
            'action'        => 'StoreTrue',
            'description'   => 'show line numbers'
        ));

        $cmd->addArgument('file', array(
            'description'   => 'spec file to show'
        ));

        return $cmd;
    }
}
