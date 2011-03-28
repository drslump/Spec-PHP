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
 * Parses and transforms spec files
 *
 * @package     Spec\Parser
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
 */
class Parser
{
    static public function parse($data, $tabsize = 4)
    {
        $tokens = token_get_all($data);

        // Order is important!
        $it = new Parser\TokenIterator($tokens, $tabsize);
        $it = new Parser\DetectKeywordsTokenIterator($it);

        $php = Parser\Transform::transform($it);
        return $php;
    }
}
