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


use DrSlump\Spec;

if (!function_exists('describe')):
/**
 * Create a group of tests
 *
 * @see Spec::describe
 * @param string $msg
 * @param callback $cb
 */
function describe($msg, $cb) {
    Spec::describe($msg, $cb);
}
endif;

if (!function_exists('it')):
/**
 * Create a test
 *
 * @see Spec::it
 * @param string $msg
 * @param callback $cb
 */
function it($msg, $cb) {
    Spec::it($msg, $cb);
}
endif;

if (!function_exists('expect')):
/**
 * Create an expectaion
 *
 * @see Spec:expect
 * @param mixed $value
 * @param bool $implicitAssert
 * @return DrSlump\Spec\ExpectAutoComplete
 */
function expect($value, $implicitAssert = true) {
    return new Spec\Expect($value, $implicitAssert);
}
endif;


if (!function_exists('all')):
/**
 * Create a wrapper for a value that makes an expectation be run
 * for all its elements.
 *
 * @param array|Iterator $iterable
 * @return Spec\ExpectAll
 */
function all($iterable) {
    if (func_num_args() > 1 || (!is_array($iterable) && !($iterable instanceof Traversable))) {
        $iterable = func_get_args();
    }
    return new Spec\ExpectAll($iterable);
}
endif;

if (!function_exists('any')):
/**
 * Create a wrapper for a value that makes an expectation be run
 * for all its elements.
 *
 * @param array|Iterator $iterable
 * @return Spec\ExpectAnt
 */
function any($iterable) {
    if (func_num_args() > 1 || (!is_array($iterable) && !($iterable instanceof Traversable))) {
        $iterable = func_get_args();
    }
    return new Spec\ExpectAny($iterable);
}
endif;

if (!function_exists('none')):
/**
 * Create a wrapper for a value that makes an expectation be run
 * for all its elements.
 *
 * @param array|Iterator $iterable
 * @return Spec\ExpectNone
 */
function none($iterable) {
    if (func_num_args() > 1 || (!is_array($iterable) && !($iterable instanceof Traversable))) {
        $iterable = func_get_args();
    }
    return new Spec\ExpectNone($iterable);
}
endif;