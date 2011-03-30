<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

/**************************************************************************
 ** IMPORTANT: Do not include this file in your code!                    **
 ** This file is only available to hint IDEs for auto completion         **
 **************************************************************************/


namespace DrSlump\Spec;

// Ensure this file is never parsed even if included by mistake
if (true) __halt_compiler();


/**
 * @todo This should be auto generated for its most part
 */
class ExpectAutoComplete extends Expect
{
    /**
     * Checks if a value is exactly equal to another one
     *
     * @param  $v
     * @return ExpectAutoComplete
     */
    function be($v){ return $this; }
    function be_eq($v){ return $this; }
    function be_equal($v){ return $this; }
    function be_at_most($v){ return $this; }
    function be_at_least($v){ return $this; }

    /**
     * Checks if a value is less than another
     *
     * @param  $v
     * @return ExpectAutoComplete
     */
    function be_less_than($v){ return $this; }

    function be_more_than($v){ return $this; }
}


// Main keywords
define('describe', 'Describe a functionality');
define('it', 'Test a functionality');
define('should', 'Define an expectation');
define('end', 'End of block');
define('before', 'Before');
define('before_each', 'Before Each');
define('after', 'After');
define('after_each', 'After Each');

// Combinators
define('and', 'Both must be true');
define('but', 'At least this should be true');
define('or', 'Any must be true');

// Common words
define('to', 'to');
define('a', 'a');
define('an', 'an');
define('be', 'be');
define('at', 'at');
define('the', 'the');
define('than', 'than');
define('is', 'is');
define('not', 'not');
define('have', 'have');
define('of', 'of');

// Common matchers
define('equal', 'equal');
define('eq', 'eq');
define('same', 'same');
define('identical', 'identical');
define('exactly', 'exactly');
define('less', 'less');
define('greater', 'greater');
define('more', 'more');
define('ge', 'ge');
define('le', 'le');
define('least', 'least');
define('most', 'most');
define('empty', 'empty');
define('type', 'type');
define('contain', 'contain');
define('key', 'key');

// Type matchers
define('integer', 'integer');
define('int', 'int');
define('float', 'float');
define('double', 'double');
define('string', 'string');
define('boolean', 'boolean');
define('bool', 'bool');
define('array', 'array');
define('object', 'object');
define('resource', 'resource');
define('scalar', 'scalar');
define('numeric', 'numeric');
define('callable', 'callable');

// Value matchers
define('null', 'null');
define('falsy', 'falsy');
define('truthy', 'truthy');


// Let's try to fool smart auto-completion

/** @var $this \PHPUnit_Framework_TestCase */
$this = new \PHPUnit_Framework_TestCase();

/** @var $W \stdClass */
$W = new \stdClass();
