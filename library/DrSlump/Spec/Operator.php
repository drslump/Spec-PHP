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
 * Defines a coordination operator (AND, OR, BUT)
 *
 * @package     Spec
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
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
