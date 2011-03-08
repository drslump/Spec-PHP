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

// IMPORTANT: Do not include this file in your code!
// This file is only available to hint IDEs for auto completion

namespace DrSlump\Spec;

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

// Value matchers
define('null', 'null');
define('falsy', 'falsy');
define('truthy', 'truthy');

