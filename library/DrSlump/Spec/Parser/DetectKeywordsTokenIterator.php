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
 * Detects Spec keywords in the TokenIterator
 *
 * @package     Spec\Parser
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
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

