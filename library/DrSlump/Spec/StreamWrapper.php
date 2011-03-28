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
 * Implements a stream wrapper for transforming spec files
 *
 * @package     Spec\Parser
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
 */
class StreamWrapper
{
    /** @var int */
    protected $offset = 0;
    /** @var string */
    protected $data;
    /** @var array */
    protected $stat;

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $path = substr($path, strpos($path, ':')+3);

        // Store stat info from the file system
        $this->stat = @stat($path);

        // Fetch physical file
        $src = @file_get_contents($path);
        if ($src === false) {
            return false;
        }

        // Process spec syntax
        $this->data = Parser::parse($src);

        return true;
    }


    public function stream_read($count)
    {
        $bytes = substr($this->data, $this->offset, $count);
        $this->offset += strlen($bytes);
        return $bytes;
    }

    public function stream_tell()
    {
        return $this->offset;
    }

    public function stream_eof()
    {
        return $this->offset >= strlen($this->data);
    }

    public function stream_stat()
    {
        return $this->stat;
    }

    public function url_stat($path, $flags)
    {
        $fname = substr($path, strpos($path, ':')+3);

        if ($flags & STREAM_URL_STAT_QUIET) {
            return @stat($fname);
        } else {
            return stat($fname);
        }
    }

    public function stream_seek($offset, $whence)
    {
        $length = strlen($this->data);
        switch ($whence) {
            case SEEK_SET:
                if ($offet >= 0 && $offset < $length) {
                    $this->offset = $offset;
                    return true;
                }
                break;

            case SEEK_CUR:
                if ($offset >= 0) {
                    $this->offset += $offset;
                    return true;
                }
                break;

            case SEEK_END:
                $offset = $length + $offset;
                if ($offset >= 0 && $offset < $length) {
                    $this->offset = $offset;
                    return true;
                }
                break;
        }

        return false;
    }
}


