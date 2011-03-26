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

namespace DrSlump\Spec\Matcher;

/**
 * Matcher that allows to define it's "matches" method with a callback
 *
 * @example
 *
 *   $matcher = new \DrSlump\Spec\Matcher\Callback();
 *   $matcher->setDescription('an odd number');
 *   $matcher->setCallback(function($v){
 *     return $v % 2;
 *   });
 *   assertThat(10, $matcher); // Fails because 10 is even
 *
 * @package     Spec\Matcher
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class Callback extends \Hamcrest_BaseMatcher
{
    /** @var array Array of arguments given to the constructor */
    protected $expected;
    /** @var string */
    protected $description = '';
    /** @var callback */
    protected $callback;

    /**
     * Creates an instance with an arbitrary number of arguments
     */
    public function __construct($expected = null)
    {
        $this->expected = func_get_args();
    }

    /**
     * @param string $description
     * @return Callback - Fluent interface
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param callback $fn
     * @return Callback - Fluent interface
     */
    public function setCallback($fn)
    {
        $this->callback = $fn;
        return $this;
    }

    /**
     * Generates a description of the object.  The description may be part
     * of a description of a larger object of which this is just a component,
     * so it should be worded appropriately.
     *
     * @param Hamcrest_Description $description
     *   The description to be built or appended to.
     */
    public function describeTo(\Hamcrest_Description $description)
    {
        if (empty($this->description)) {
            foreach ($this->expected as $expected) {
                $description->appendValue($expected);
            }
        } else {
            $description->appendText($this->description);
        }
    }

    /**
     * Evaluates the matcher for argument <var>$item</var>.
     *
     * @param mixed $item the object against which the matcher is evaluated.
     *
     * @return boolean <code>true</code> if <var>$item</var> matches,
     *   otherwise <code>false</code>.
     *
     * @see Hamcrest_BaseMatcher
     */
    public function matches($item)
    {
        if (!is_callable($this->callback)) {
            throw new \Exception( __CLASS__ . ' callback not set or not valid');
        }

        $args = array_merge(array($item), $this->expected);
        return (bool) call_user_func_array($this->callback, $args);
    }
}
