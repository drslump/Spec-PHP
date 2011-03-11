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
 * Creates an iterator from an array returned from token_get_all()
 *
 * @package     Spec\Parser
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class TokenIterator extends \ArrayIterator
{
    /** @var int */
    protected $tabsize = 4;

    /**
     * @param array $array  An array as returned by token_get_all()
     * @param int $tabsize  Number of spaces from a tab
     */
    public function __construct(array $array, $tabsize = 4)
    {
        $this->tabsize = 4;

        $quoted = '';
        $inQuoted = false;
        foreach ($array as $token) {
            // Simplify double quoted strings with variables
            if (!is_array($token) && $token === '"') {
                if ($inQuoted) {
                    $quoted .= $token;
                    $inQuoted = false;
                    $token = array(T_CONSTANT_ENCAPSED_STRING, $quoted, null);
                } else {
                    $quoted = $token;
                    $inQuoted = true;
                }
            } else if ($inQuoted) {
                $quoted .= is_array($token) ? $token[1] : $token;
                continue;
            }

            $this->_insertToken($token);
        }
    }

    /**
     * @param array $token
     */
    protected function _insertToken($token)
    {
        $result = new Token();

        if (!is_array($token)) {
            switch ($token) {
                case '.': $result->type = Token::DOT; break;
                case ',': $result->type = Token::COMMA; break;
                case '(': $result->type = Token::LPAREN; break;
                case ')': $result->type = Token::RPAREN; break;
                case '{': $result->type = Token::LCURLY; break;
                case '}': $result->type = Token::RCURLY; break;
                case ';': $result->type = Token::SEMICOLON; break;
                default:  $result->type = Token::TEXT;
            }

            $result->value = $token;
            $result->token = null;
            $result->line = null;

        } else {

            $result->token = $token[0];
            $result->line = $token[2];

            switch ($token[0]) {
                // Convert hard-tabs to soft-tabs and \r\n to \n
                case T_WHITESPACE:
                    $lines = str_replace(
                        array("\t", "\r\n", "\r"),
                        array(str_repeat(' ', $this->tabsize), "\n", "\n"),
                        $token[1]
                    );
                    $lines = explode("\n", $lines);
                    foreach ($lines as $idx=>$line) {
                        if ($idx > 0) {
                            $result = new Token(Token::EOL, "\n");
                            $this->append($result);
                        }

                        $result = new Token(Token::WHITESPACE, $line);
                        $this->append($result);
                    }
                    return;


                case T_VARIABLE:
                    $result->type = Token::VARIABLE;
                    $result->value = $token[1];
                    break;
                case T_STRING:
                    $result->type = Token::IDENT;
                    $result->value = $token[1];
                    break;
                case T_CONSTANT_ENCAPSED_STRING:
                    $result->type = Token::QUOTED;
                    $result->value = $token[1];
                    break;
                case T_LNUMBER:
                case T_DNUMBER:
                    $result->type = Token::NUMBER;
                    $result->value = $token[1];
                    break;
                case T_COMMENT:
                case T_DOC_COMMENT:
                    $result->type = Token::COMMENT;
                    $result->value = $token[1];
                    break;

                default:
                    $result->type = Token::TEXT;
                    $result->value = $token[1];
            }

        }


        $this->append($result);
    }

}
