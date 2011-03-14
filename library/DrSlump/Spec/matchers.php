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

// Include Hamcrest matchers library
require_once 'Hamcrest/MatcherAssert.php';
require_once 'Hamcrest/Matchers.php';


// Define standard set of matchers

Spec::registerMatcher(
    array('equal', 'eq'),
    '\Hamcrest_Matchers::equalTo'
);

Spec::registerMatcher(
    array('same', 'identical', 'exactly', 'exactly_equal'),
    '\Hamcrest_Matchers::identicalTo'
);

Spec::registerMatcher(
    array('at_most', 'most', 'less_equal', 'le'),
    '\Hamcrest_Matchers::lessThanOrEqualTo'
);

Spec::registerMatcher(
    array('at_least', 'least', 'more_equal', 'ge'),
    '\Hamcrest_Matchers::greaterThanOrEqualTo'
);

Spec::registerMatcher(
    array('greater', 'more'),
    '\Hamcrest_Matchers::greaterThan'
);

Spec::registerMatcher(
    array('less'),
    '\Hamcrest_Matchers::lessThan'
);

Spec::registerMatcher(
    array('instance', 'instance_of'),
    function($expected){
        return \Hamcrest_Matchers::anInstanceOf($expected);
    }
);

Spec::registerMatcher(
    array('empty_string'),
    '\Hamcrest_Matchers::isEmptyString'
);

Spec::registerMatcher(
    array('empty_array'),
    '\Hamcrest_Matchers::emptyArray'
);

Spec::registerMatcher(
    array('type', 'of_type', 'typeof', 'type_of', 'has_type', 'have_type'),
    '\Hamcrest_Matchers::typeOf'
);

Spec::registerMatcher(
    array('array'),
    '\Hamcrest_Matchers::arrayValue'
);

Spec::registerMatcher(
    array('string'),
    '\Hamcrest_Matchers::stringValue'
);

Spec::registerMatcher(
    array('boolean', 'bool'),
    '\Hamcrest_Matchers::booleanValue'
);

Spec::registerMatcher(
    array('double'),
    '\Hamcrest_Matchers::doubleValue'
);

Spec::registerMatcher(
    array('float'),
    '\Hamcrest_Matchers::floatValue'
);

Spec::registerMatcher(
    array('numeric'),
    '\Hamcrest_Matchers::numericValue'
);

Spec::registerMatcher(
    array('integer', 'int'),
    '\Hamcrest_Matchers::integerValue'
);

Spec::registerMatcher(
    array('object'),
    '\Hamcrest_Matchers::objectValue'
);

Spec::registerMatcher(
    array('resource'),
    '\Hamcrest_Matchers::resourceValue'
);

Spec::registerMatcher(
    array('scalar'),
    '\Hamcrest_Matchers::scalarValue'
);

Spec::registerMatcher(
    array('callable'),
    '\Hamcrest_Matchers::callable'
);

Spec::registerMatcher(
    array('null', 'nil'),
    '\Hamcrest_Matchers::nullValue'
);

Spec::registerMatcher(
    array('contain', 'contains', 'have_item'),
    '\Hamcrest_Matchers::hasItemInArray'
);

Spec::registerMatcher(
    array('have_key', 'contain_key', 'contains_key'),
    '\Hamcrest_Matchers::hasKeyInArray'
);

// Example matcher with callback
Spec::registerMatcher(
    array('odd'),
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
Spec::registerMatcher(
    array('case_insensitive_equal', 'nocase_equal', 'equal_nocase', 'nocase_eq', 'eq_nocase'),
    function($expected){
        $matcher = new DrSlump\Spec\Matcher\Callback($expected);
        $matcher->setDescription('equal (case insensitive) to ' . $expected);
        $matcher->setCallback(function($v, $expected){
            return strcasecmp($v, $expected) === 0;
        });
        return $matcher;
    }
);