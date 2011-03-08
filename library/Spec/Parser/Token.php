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

namespace DrSlump\Spec\Parser;

/**
 * Represents a token
 *
 * @package     Spec\Parser
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class Token extends \ArrayIterator
{
    const WHITESPACE    = 'WHITESPACE';
    const EOL           = 'EOL';
    const TEXT          = 'TEXT';
    const COMMENT       = 'COMMENT';
    const DOT           = 'DOT';
    const COMMA         = 'COMMA';
    const SEMICOLON     = 'SEMICOLON';
    const LPAREN        = 'LPAREN';
    const RPAREN        = 'RPAREN';
    const LCURLY        = 'LCURLY';
    const RCURLY        = 'RCURLY';
    const QUOTED        = 'QUOTED';
    const NUMBER        = 'NUMBER';
    const VARIABLE      = 'VARIABLE';
    const IDENT         = 'IDENT';
    const FUNCTIONCALL  = 'FUNCTIONCALL';
    const DESCRIBE      = 'DESCRIBE';
    const IT            = 'IT';
    const SHOULD        = 'SHOULD';
    const END           = 'END';

    /** @var string */
    public $type = self::TEXT;

    /** @var string */
    public $value = '';

    /**
     * @param string $type
     * @param string $value
     */
    public function __construct($type = self::TEXT, $value = '')
    {
        $this->type = $type;
        $this->value = $value;
    }
}

