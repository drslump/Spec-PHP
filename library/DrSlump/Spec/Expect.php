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
 * Expection class
 *
 * @package     Spec
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class Expect
{

    /** @var \Hamcrest_Matcher */
    protected $subject;
    /** @var String */
    protected $message;
    /** @var bool */
    protected $implicitAssert = true;

    /** @var array */
    protected $expression = array();

    /** @var array Words to ignore */
    protected $ignoredWords = array(
        'to', 'be', 'is', 'a', 'an', 'the', 'than',
    );



    /**
     * @param mixed $value
     * @param bool $implicitAssert
     */
    public function __construct($value, $implicitAssert = true)
    {
        if (!($value instanceof ExpectInterface)) {
            $value = new ExpectIt($value);
        }

        $this->subject = $value;
        $this->implicitAssert = $implicitAssert;
    }

    /**
     * Describe the expectation with a custom message
     *
     * @param String $msg
     */
    public function describedAs($msg)
    {
        $this->message = $msg;
        return $this;
    }


    /**
     * ->to_be_type('string')->or('bool')->or('null')
     * should have type 'string', 'bool' or 'null'
     *        \----------------------------------/
     * ->to_be_equal()->or_less(10) ==> ->to_be_equal_or_less(10)
     * should be equal or less than 10
     *           \-------------------/
     * ->to_be_truly_or_null()
     * should be truly or null
     *        \--------------/
     * ->to_be_instance_of('MyClass')->and_have_property('foo')
     * should be instance of 'MyClass' and have property 'foo'
     *           \-------------------------------------------/
     * ->to_be_integer()->and_greater_than(10)->and_less_than_or_equal_to(20)
     * should be an integer and greater than 10 and less than or equal to 20
     *           \------------------------------------------/    \---------/
     * ->to_equal(1)->or(3)->or(4)->but_not_string()
     * should equal 1, 3 or 4 but not be string
     *        \-------------/     \-----------/
     *
     * ->to_be_integer_and_equal(10)->or_boolean_and_true()
     * should be an integer and equal 10 or a boolean and be true
     *           \---------------------/    \-------------------/
     * ->to_be_greater(10)->or_equal(10)->but_less(20)->or_equal(20)
     * should be greater than 10 or equal to 10 but less than 20 or equal to 20
     *           \----------------------------/     \-------------------------/
     * ->to_be_integer_or_string_and_numeric_or_null()
     * should be integer or string and numeric or null
     *           \-----/    \----------------/    \--/
     * ->to_be_integer_or_string_and_numeric_but_less(10)
     * should be integer or string and numeric, and be less than 10
     *           \-----/    \----------------/         \----------/
     *           \---------------------------/         \----------/
     *
     *
     * Coordination by and and or is governed by the standard binding order of logic,
     * i.e. and binds stronger than or. Commas can be used to override the standard
     * binding order
     *
     *    and > or > ,and / but > ,or
     *
     *    "but" is an alias of ",and"
     *
     *
     * Commas without and/or just after assume an "or" was given:
     *
     *      be an integer, float and empty --> integer or (float and empty)
     *
     *
     *
     * should be an integer or a boolean, and be truthy
     *        \-------------------------/ \------------/
     *
     * should be an integer, a string or a boolean, and not be empty
     *        \----------------------------------/  \--------------/
     *
     * should be an integer or a boolean and not empty
     *        \-----------/    \---------------------/
     *
     * should be an integer, a string or a boolean and not empty
     *        \---------------------/    \----------------------/
     *
     * should have one of "one", "two" or "three", and be string
     *        \---------------------------------/      \-------/
     *
     *
     * Magic PHP method to handle any method name not defined in
     * the class.
     *
     * @param String $fn
     * @param Array $args
     * @return Expect
     */
    public function __call($fn, $args)
    {
        // Define aliases for method names not supported statically by PHP
        switch(strtolower($fn)) {
        case 'as':
            return call_user_func_array(array($this, 'describedAs'), $args);
        case 'do':
            return call_user_func_array(array($this, 'doAssert'), $args);
        }

        // Any other method name is passed to "assert"
        return $this->assert($fn, $args);
    }

    /**
     * For expectations not needing arguments (integer, empty, truthy...)
     *
     * @example
     *  expect(1)->to_be_integer->or_string->and_equal(10)
     *
     * @param  $name
     * @return Expect
     */
    public function __get($name)
    {
        return $this->assert($name, array());
    }


    public function assert($name, $args)
    {
        $name = trim($name, '_');

        // Extract ORs/ANDs/BUTs/AS from name
        if (preg_match('/^[a-z]_(or|and|but|as)$/i', $name)) {
            // We need to disable implicit assertion if set
            $origImplicit = $this->implicitAssert;
            $this->implicitAssert = false;

            $parts = preg_split('/\b(or|and|but|as)\b/i', $name, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            $prefix = '';
            do {
                $part = array_shift($parts);
                if (empty($part)) break; // Should be an "as"
                $this->assert(
                    $prefix . $part,
                    count($parts) ? array() : $args
                );
                $prefix = array_shift($parts);
            } while (count($parts));

            if (strtolower($prefix) === 'as') {
                $this->describedAs($args[0]);
            }

            if ($origImplicit) {
                $this->implicitAssert = true;
                $this->doAssert();
            }
            return $this;
        }


        // Convert camelCase to underscores
        $name = preg_replace_callback('/([a-z])([A-Z])/', function($m){
            return $m[1] . '_' . strtolower($m[2]);
        }, $name);

        // Make it all lowercase
        $name = strtolower($name);

        // Explode by the underscore
        $parts = explode('_', $name);

        // Calculate if it's a negation
        $isNegation = false;
        foreach ($parts as $part) {
            if ($part === 'not' || $part == 'no') {
                $isNegation = !$isNegation;
            }
        }

        // Remove ignored parts (including negations)
        $ignored = array_merge($this->ignoredWords, array('no', 'not'));
        $parts = array_filter($parts, function($part) use ($ignored) {
            return !in_array($part, $ignored);
        });

        // Re-index array just in case
        $parts = array_values($parts);


        // By default use "same"
        if (empty($parts)) {
            $parts[] = 'same';
        }


        // Manage coordination operators
        switch ($parts[0]) {
        case 'described':
            if (empty($parts[1]) || $parts[1] !== 'as') {
                break;
            }
        case 'as':
            $this->describedAs($args[0]);
            return $this;

        case 'and':
            $this->expression[] = new Operator('AND', 10);
            array_shift($parts);
            break;
        case 'or':
            $this->expression[] = new Operator('OR', 5);
            array_shift($parts);
            break;
        case 'but':
            $this->expression[] = new Operator('BUT', 1);
            array_shift($parts);
            break;
        }

        // @todo If $parts is empty reuse previous matcher
        // example: expect(1)->to_equal(0)->or(1);

        // Generate a matcher name from the parts
        $matcher = implode('_', $parts);
        // @todo Find the longest match
        //       This should be solved when the matchers get refactored
        //       into a "broker" or "locator" object
        if (!Spec::hasMatcher($matcher)) {
            $matches = array();
            $from = str_replace('_', ' ', $matcher);
            $ops = Spec::getMatcherNames();
            foreach ($ops as $op) {
                $to = str_replace('_', ' ', $op);
                $similarity = similar_text($from, $to);
                if ($similarity >= 7) {
                    $matches[$op] = $similarity;
                }
                //echo "Similarity $operation -> $op = $similarity\n";
            }

            $msg = "Expect matcher $matcher ($name) not found.";
            if (!empty($matches)) {
                asort($matches);
                $match = array_pop(array_keys($matches));
                $msg .= " Perhaps you meant to use $match?";
            }

            throw new \Exception($msg);
        }


        // Get callback and execute it
        $cb = Spec::getMatcher($matcher);

        $matcher = call_user_func_array($cb, $args);
        if ($isNegation) {
            $matcher = \Hamcrest_Core_IsNot::not($matcher);
        }

        $this->expression[] = $matcher;

        // Run the assertion now if the implicit flag is set
        if ($this->implicitAssert) {
            $this->doAssert();
        }

        return $this;
    }

    /**
     * Perform the assertion for the configured expression.
     *
     * It will apply binding rules to combinators (AND, OR, BUT)
     *
     * @throws \RuntimeException
     */
    public function doAssert()
    {
        // Apply Shunting Yard algorithm to convert the expression
        // into Reverse Polish Notation. Since we have a very simple
        // set of operators and binding rules the implementation becomes
        // very simple
        $ops = new \SplStack();
        $rpn = array();
        foreach ($this->expression as $token) {
            if ($token instanceof Operator) {
                while(!$ops->isEmpty() && $token->compare($ops->top()) <= 0) {
                    $rpn[] = $ops->pop();
                }
                $ops->push($token);
            } else {
                $rpn[] = $token;
            }
        }
        // Append the remaining operators
        while(!$ops->isEmpty()) {
            $rpn[] = $ops->pop();
        }

        // Walk the RPN expression to create AnyOf and AllOf matchers
        $stack = array();
        foreach ($rpn as $token) {
            if ($token instanceof Operator) {

                // Our operators always need two operands
                $operands = array(
                    array_pop($stack),
                    array_pop($stack),
                );

                switch ($token->getKeyword()) {
                case 'AND':
                case 'BUT':
                    $matcher = new \Hamcrest_Core_AllOf($operands);
                    $stack[] = $matcher;
                    break;
                case 'OR':
                    $matcher = new \Hamcrest_Core_AnyOf($operands);
                    $stack[] = $matcher;
                    break;
                }
            } else {
                $stack[] = $token;
            }
        }

        if (count($stack) !== 1) {
            throw new \RuntimeException('The RPN stack should have only one item');
        }

        $matcher = array_pop($stack);

        $this->subject->doAssert($matcher, $this->message);
    }

}
