<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/


use DrSlump\Spec;

// Include Hamcrest matchers library
require_once 'Hamcrest/MatcherAssert.php';
require_once 'Hamcrest/Matchers.php';
// Hamcrest does not include these ones by default
require_once 'Hamcrest/Type/IsNumeric.php';
require_once 'Hamcrest/Type/IsCallable.php';


// Matcher names should be written as if they were to complete the
// sentence "value should ...". Words like 'be', 'to', 'at', 'the' ...
// will be automatically ignored but when Spec finds two conflicting
// matchers they will be used to disambiguate.

$matchers = Spec::matchers();

$matchers['be equal to'] = '\Hamcrest_Matchers::equalTo';
$matchers['be eq to'] = '\Hamcrest_Matchers::equalTo';

$matchers['be the same to'] = '\Hamcrest_Matchers::identicalTo';
$matchers['be identical to'] = '\Hamcrest_Matchers::identicalTo';
$matchers['be exactly'] = '\Hamcrest_Matchers::identicalTo';
$matchers['be exactly equal to'] = '\Hamcrest_Matchers::identicalTo';

$matchers['be at most'] = '\Hamcrest_Matchers::lessThanOrEqualTo';
$matchers['be less equal to'] = '\Hamcrest_Matchers::lessThanOrEqualTo';
$matchers['be less equal than'] = '\Hamcrest_Matchers::lessThanOrEqualTo';
$matchers['be le to'] = '\Hamcrest_Matchers::lessThanOrEqualTo';
$matchers['be le than'] = '\Hamcrest_Matchers::lessThanOrEqualTo';

$matchers['be at least'] = '\Hamcrest_Matchers::greaterThanOrEqualTo';
$matchers['be more equal to'] = '\Hamcrest_Matchers::greaterThanOrEqualTo';
$matchers['be more equal than'] = '\Hamcrest_Matchers::greaterThanOrEqualTo';
$matchers['be greater equal to'] = '\Hamcrest_Matchers::greaterThanOrEqualTo';
$matchers['be greater equal than'] = '\Hamcrest_Matchers::greaterThanOrEqualTo';
$matchers['be ge to'] = '\Hamcrest_Matchers::greaterThanOrEqualTo';
$matchers['be ge than'] = '\Hamcrest_Matchers::greaterThanOrEqualTo';

$matchers['be greater than'] = '\Hamcrest_Matchers::greaterThan';
$matchers['be more than'] = '\Hamcrest_Matchers::greaterThan';

$matchers['be less than'] = '\Hamcrest_Matchers::lessThan';

$matchers['be an instance of'] = '\Hamcrest_Matchers::anInstanceOf';
$matchers['be an instanceof'] = '\Hamcrest_Matchers::anInstanceOf';

$matchers['be an empty string'] = '\Hamcrest_Matchers::isEmptyString';
$matchers['be an empty array'] = '\Hamcrest_Matchers::emptyArray';

$matchers['be of type'] = '\Hamcrest_Matchers::typeOf';
$matchers['has type of'] = '\Hamcrest_Matchers::typeOf';
$matchers['have type of'] = '\Hamcrest_Matchers::typeOf';

$matchers['be an array'] = '\Hamcrest_Matchers::arrayValue';
$matchers['be a string'] = '\Hamcrest_Matchers::stringValue';
$matchers['be a boolean'] = '\Hamcrest_Matchers::booleanValue';
$matchers['be a bool'] = '\Hamcrest_Matchers::booleanValue';
$matchers['be a double'] = '\Hamcrest_Matchers::doubleValue';
$matchers['be a float'] = '\Hamcrest_Matchers::floatValue';
$matchers['be an integer'] = '\Hamcrest_Matchers::integerValue';
$matchers['be an int'] = '\Hamcrest_Matchers::integerValue';
$matchers['be an object'] = '\Hamcrest_Matchers::objectValue';
$matchers['be a resource'] = '\Hamcrest_Matchers::resourceValue';

$matchers['be a scalar'] = '\Hamcrest_Matchers::scalarValue';
$matchers['be a scalar value'] = '\Hamcrest_Matchers::scalarValue';
$matchers['be numeric'] = '\Hamcrest_Matchers::numericValue';
$matchers['be a numeric value'] = '\Hamcrest_Matchers::numericValue';
$matchers['be callable'] = '\Hamcrest_Matchers::callable';
$matchers['be a callable value'] = '\Hamcrest_Matchers::callable';

$matchers['be a null'] = '\Hamcrest_Matchers::nullValue';
$matchers['be a null value'] = '\Hamcrest_Matchers::nullValue';
$matchers['be a nil'] = '\Hamcrest_Matchers::nullValue';
$matchers['be a nil value'] = '\Hamcrest_Matchers::nullValue';

$matchers['be a true'] = '\DrSlump\Spec\Matcher\True::trueValue';
$matchers['be a true value'] = '\DrSlump\Spec\Matcher\True::trueValue';

$matchers['be truthy'] = '\DrSlump\Spec\Matcher\Truthy::truthyValue';
$matchers['be a truthy value'] = '\DrSlump\Spec\Matcher\Truthy::truthyValue';
$matchers['be truly'] = '\DrSlump\Spec\Matcher\Truthy::truthyValue';
$matchers['be a truly value'] = '\DrSlump\Spec\Matcher\Truthy::truthyValue';

$matchers['be a false'] = '\DrSlump\Spec\Matcher\False::falseValue';
$matchers['be a false value'] = '\DrSlump\Spec\Matcher\False::falseValue';

$matchers['be falsy'] = '\DrSlump\Spec\Matcher\Falsy::falsyValue';
$matchers['be a falsy value'] = '\DrSlump\Spec\Matcher\Falsy::falsyValue';

$matchers['be empty'] = '\DrSlump\Spec\Matcher\IsEmpty::emptyValue';
$matchers['be an empty value'] = '\DrSlump\Spec\Matcher\IsEmpty::emptyValue';

$matchers['contain'] = '\Hamcrest_Matchers::hasItemInArray';
$matchers['have an item'] = '\Hamcrest_Matchers::hasItemInArray';
$matchers['have an item like'] = '\Hamcrest_Matchers::hasItemInArray';

$matchers['contain the key'] = '\Hamcrest_Matchers::hasKeyInArray';
$matchers['have the key'] = '\Hamcrest_Matchers::hasKeyInArray';


// Example matcher with callback
$matchers->register(
    array('be odd', 'be an odd value'),
    function(){
        $matcher = new DrSlump\Spec\Matcher\Callback();
        $matcher->setDescription('an odd number');
        $matcher->setCallback(function($v){
            return $v % 2;
        });
        return $matcher;
    }
);

// Example matcher with expected value and callback
$matchers->register(
    array('be case insensitive equal', 'be nocase equal', 'be equal nocase', 'be nocase eq', 'be eq nocase'),
    function($expected){
        $matcher = new DrSlump\Spec\Matcher\Callback($expected);
        $matcher->setDescription('equal (case insensitive) to ' . $expected);
        $matcher->setCallback(function($v, $expected){
            return strcasecmp($v, $expected) === 0;
        });
        return $matcher;
    }
);