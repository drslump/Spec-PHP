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

/**
 * Implements a stream wrapper for transforming spec files
 *
 * @package     Spec\Parser
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
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


