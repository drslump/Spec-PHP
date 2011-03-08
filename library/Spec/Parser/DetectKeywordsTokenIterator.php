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
 * Detects Spec keywords in the TokenIterator
 *
 * @package     Spec\Parser
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class DetectKeywordsTokenIterator extends \IteratorIterator implements \SeekableIterator
{
    public function current()
    {
        $token = parent::current();

        if ($token->type === Token::IDENT) {
            switch (strtolower($token->value)) {
            case 'describe':
                $token->type = Token::DESCRIBE;
                break;
            case 'it':
                $token->type = Token::IT;
                break;
            case 'should':
                $token->type = Token::SHOULD;
                break;
            case 'end':
                $token->type = Token::END;
                break;
            }
        }

        return $token;
    }

    public function seek($ofs)
    {
        $this->getInnerIterator()->seek($ofs);
    }
}

