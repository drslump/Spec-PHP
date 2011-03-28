<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/


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