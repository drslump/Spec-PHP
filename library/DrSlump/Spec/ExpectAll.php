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

use DrSlump\Spec;


/**
 * Wraps an iterable variable to apply an expectation over all of
 * its members, and it will be ok if all of them pass.
 *
 * @package     Spec
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class ExpectAll implements ExpectInterface
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function doAssert(\Hamcrest_Matcher $matcher, $message = null)
    {
        if (!empty($message)) {
            \Hamcrest_MatcherAssert::assertThat(
                $message,
                $this->value,
                \Hamcrest_Core_Every::everyItem($matcher)
            );
        } else {
            \Hamcrest_MatcherAssert::assertThat(
                $this->value,
                \Hamcrest_Core_Every::everyItem($matcher)
            );
        }
    }
}
