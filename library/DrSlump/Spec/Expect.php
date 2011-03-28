<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

namespace DrSlump\Spec;

use DrSlump\Spec;


/**
 * Expectation class
 *
 * @package     Spec
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
 */
class Expect
{

    /** @var \Hamcrest_Matcher */
    protected $subject;
    /** @var String */
    protected $message;
    /** @var bool */
    protected $implicitAssert = true;

    /** @var \DrSlump\Spec\Expression */
    protected $expression;
    /** @var string */
    protected $prevMatcher;

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

        $this->expression = new Expression();
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
     * Magic PHP method to handle any method name not defined in
     * the class.
     *
     * @param String $fn
     * @param Array $args
     * @return ExpectAutoComplete
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
     * @param string $name
     * @return Expect
     */
    public function __get($name)
    {
        return $this->assert($name, array());
    }


    public function assert($name, $args)
    {
        $name = trim($name, '_');

        // Convert camelCase to underscores
        $name = preg_replace_callback('/([a-z])([A-Z])/', function($m){
            return $m[1] . '_' . $m[2];
        }, $name);

        // Make it all lowercase
        $name = strtolower($name);

        // Remove 'to' if it's at the beginning since it might be used
        // when manually calling expect()
        $name = preg_replace('/^to_/', '', $name);

        // Extract ORs/ANDs/BUTs/AS from name
        if (preg_match('/^[a-z]+_(or|and|but|as)_?$/i', $name)) {
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


        // Explode by the underscore
        $parts = explode('_', $name);

        // Calculate if it's a negation
        $isNegation = false;
        foreach ($parts as $part) {
            if ($part === 'not' || $part == 'no') {
                $isNegation = !$isNegation;
            }
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
            $this->expression->addOperator('AND', 10);
            array_shift($parts);
            break;
        case 'or':
            $this->expression->addOperator('OR', 5);
            array_shift($parts);
            break;
        case 'but':
            $this->expression->addOperator('BUT', 1);
            array_shift($parts);
            break;
        default:
            // If no operator was given assume OR
            if (NULL !== $this->prevMatcher) {
                $this->expression->addOperator('OR', 5);
            }
        }

        // If nothing left reuse previous matcher
        if (empty($parts)) {
            if (NULL === $this->prevMatcher) {
                throw new \RuntimeException("Unable to re-use previous matcher since it's empty");
            } else if (empty($args)) {
                throw new \RuntimeException("Unable to re-use previous matcher without arguments being given");
            }
            $matcher = $this->prevMatcher;
        } else {
            $matcher = implode(' ', $parts);
            $this->prevMatcher = $matcher;
        }

        // Find the matcher for the given name
        $callback = Spec::matchers()->find($matcher);
        if (FALSE === $callback) {

            $msg = "Matcher '" . str_replace('_', ' ', $name) . "' not found.";

            $suggestions = Spec::matchers()->suggest($name, 0.5);
            if (count($suggestions)) {
                $msg .= " Perhaps you meant to use '". array_shift($suggestions) . "' ?";
            }

            throw new \Exception($msg);
        }

        // Instantiate the matcher
        $matcher = call_user_func_array($callback, $args);
        if ($isNegation) {
            $matcher = \Hamcrest_Core_IsNot::not($matcher);
        }

        $this->expression->addOperand($matcher);

        // Run the assertion now if the implicit flag is set
        if ($this->implicitAssert) {
            $this->doAssert();
        }

        return $this;
    }

    /**
     * Perform the assertion for the configured expression.
     *
     * @throws \RuntimeException
     */
    public function doAssert()
    {
        $matcher = $this->expression->build();
        $this->subject->doAssert($matcher, $this->message);
    }

}
