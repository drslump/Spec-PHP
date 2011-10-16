<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

namespace DrSlump\Spec\Parser;

/**
 * Represents a token
 *
 * @package     Spec\Parser
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
 */
class Token extends \ArrayIterator
{
    const WHITESPACE    = 'WHITESPACE';
    const EOL           = 'EOL';
    const TEXT          = 'TEXT';
    const COMMENT       = 'COMMENT';
    const DOT           = 'DOT';
    const COMMA         = 'COMMA';
    const COLON         = 'COLON';
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
    const SHOULD        = 'SHOULD';
    const END           = 'END';

    /** @var string */
    public $type = self::TEXT;

    /** @var string */
    public $value = '';

    /** @var int PHP's tokenizer token code */
    public $token = null;

    /** @var int PHP's tokenizer reported line */
    public $line = null;

    /**
     * @param string $type
     * @param string $value
     */
    public function __construct($type = self::TEXT, $value = '', $token = null, $line = null)
    {
        $this->type = $type;
        $this->value = $value;
        $this->token = $token;
        $this->line = $line;
    }
}

