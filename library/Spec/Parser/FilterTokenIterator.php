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
 * Filter a TokenIterator to simplify some tokens
 *
 * @todo Can this functionality be move to the parser?
 *
 * @package     Spec\Parser
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class FilterTokenIterator extends \FilterIterator
{
    public function accept()
    {
        $it = $this->getInnerIterator();
        $token = $it->current();

        if ($token->type === Token::DOT) {
            $types = array(
                Token::DESCRIBE,
                Token::IT,
                Token::SHOULD,
            );

            return !$this->hasBefore($types);

            // False if found after or before
            return !($this->hasAfter($types) || $this->hasBefore($types));
        }

        if ($token->type === Token::SEMICOLON) {
            $types = array(
                Token::DESCRIBE,
                Token::IT,
            );
            $skip = array(
                Token::WHITESPACE,
                Token::QUOTED,
                Token::DOT,
            );

            return !$this->hasBefore($types, $skip);
        }

        return true;
    }



    protected function hasBefore($types, $skip = Token::WHITESPACE)
    {
        if (!is_array($types))
            $types = array($types);
        if (!is_array($skip))
            $skip = $skip ? array($skip) : array();

        $it = $this->getInnerIterator();
        $ofs = $it->key();

        for ($i=$ofs-2; $i>=0; $i--) {
            $it->seek($i);
            $it->next();
            $token = $it->current();

            //echo "Checking $i - $token->type **$token->value**\n";

            if (in_array($token->type, $types, true)) {
                $it->seek($ofs);
                return true;
            }

            if (!in_array($token->type, $skip, true)) {
                 break;
            }
        }

        $it->seek($ofs);
        return false;
    }

    protected function hasAfter($types, $skip = Token::WHITESPACE)
    {
        if (!is_array($types))
            $types = array($types);
        if (!is_array($skip))
            $skip = $skip ? array($skip) : array();

        $it = $this->getInnerIterator();
        $ofs = $it->key();

        $it->next();
        while ($it->valid()) {
            $token = $it->current();

            if (in_array($token->type, $types, true)) {
                $it->seek($ofs);
                return true;
            }

            if (!in_array($token->type, $skip, true)) {
                 break;
            }

            $it->next();
        }

        $it->seek($ofs);
        return false;
    }

}