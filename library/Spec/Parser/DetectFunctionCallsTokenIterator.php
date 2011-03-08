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
 * Detects function calls in the TokenIterator
 *
 * @package     Spec\Parser
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class DetectFunctionCallsTokenIterator extends \IteratorIterator implements \SeekableIterator
{
    public function current()
    {
        $it = $this->getInnerIterator();
        $token = $it->current();

        if ($token->type === Token::IDENT) {

            $currentOfs = $it->key();

            $it->next();
            while ($it->valid()) {
                $next = $it->current();
                switch ($next->type) {
                case Token::LPAREN:
                    // Function call found
                    $token->type = Token::FUNCTIONCALL;
                    break 2;
                case Token::WHITESPACE:
                case Token::DOT:
                    // Ignore them
                    break;
                default:
                    // Anything stops the check
                    break 2;
                }

                $this->next();
            }

            $this->seek($currentOfs);
        }

        return $token;
    }

    public function seek($ofs)
    {
        $this->getInnerIterator()->seek($ofs);
    }
}

