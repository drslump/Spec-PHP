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
 * Defines a coordination operator (AND, OR, BUT)
 *
 * @package     Spec
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
 */
class Operator
{
    protected $priority = 0;
    protected $keyword = '';

    public function __construct($keyword, $priority)
    {
        $this->keyword = $keyword;
        $this->priority = $priority;
    }

    public function getKeyword()
    {
        return $this->keyword;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function compare(Operator $op)
    {
        if ($this->priority < $op->getPriority()) {
            return -1;
        } else if ($this->priority > $op->getPriority()) {
            return 1;
        } else {
            return 0;
        }
    }
}
