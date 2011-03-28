<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

namespace DrSlump\Spec;

/**
 * Serves as container and locator for matchers
 *
 * @package     Spec
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
 */
class MatcherBroker implements \ArrayAccess
{
    /** @var array() */
    protected $registry = array();

    /** @var array() */
    protected $filtered = array();

    /** @var array */
    protected $ignored = array(
        'to', 'be', 'is', 'a', 'an', 'at', 'the', 'of', 'than', 'no', 'not',
    );

    /**
     * @param array|string $names
     * @param callable $matcher
     */
    public function register($names, $matcher)
    {
        if (!is_array($names)) $names = array($names);

        foreach ($names as $name) {
            $this[$name] = $matcher;
        }
    }

    /**
     *
     * @param array|string $names
     */
    public function unregister($names)
    {
        if (!is_array($names)) $names = array($names);

        foreach ($names as $name) {
            unset($this[$name]);
        }
    }

    /**
     * Filters a matcher name by removing ignored words
     *
     * @param string $name
     * @return string
     */
    protected function filter($name)
    {
        // Remove ignored words
        $name = strtolower($name);
        $parts = explode(' ', $name);
        $parts = array_diff($parts, $this->ignored);
        return implode(' ', $parts);
    }

    /**
     * Tries to find a suitable matcher
     *
     * @param string $name
     * @return false | callable
     */
    public function find($name)
    {
        // Normalize the name
        $find = strtolower($name);
        $find = str_replace('_', ' ', $find);

        // Remove ignored words, if the result is empty use "same"
        $find = $this->filter($find);
        if (empty($find)) {
            $find = 'same';
        }

        // Iterate over all registered matchers to find candidates
        $candidates = array();
        foreach ($this->filtered as $k=>$filtered) {
            if ($find === $filtered) {
                $candidates[] = $k;
            }
        }

        // Nothing matches the search :(
        if (empty($candidates)) {
            return false;
        }

        // Find the one that best matches the original
        $selected = null;
        $best = 0;
        foreach ($candidates as $candidate) {
            $similarity = similar_text($name, $candidate);
            if ($similarity > $best) {
                $best = $similarity;
                $selected = $candidate;
            }
        }

        return $this[$selected];
    }

    /**
     * Obtain a list of suggestions for matcher names
     *
     * @param string $name
     * @return array
     */
    public function suggest($name, $percentage = 0.7)
    {
        // Normalize the name
        $name = strtolower($name);
        $name = str_replace('_', ' ', $name);

        $percentage = $percentage * 100;

        // Iterate over all registered matchers to meassure candidates
        $suggestions = array();
        foreach ($this->filtered as $k=>$filtered) {
            similar_text($name, $k, $similarity);
            if ($similarity >= $percentage) {
                $suggestions[$k] = $similarity;
            }
        }

        // Sort the suggestions by similarity (bigger first)
        arsort($suggestions, SORT_NUMERIC);

        return array_keys($suggestions);
    }


    /**
     * Whether a offset exists
     *
     * @param mixed $offset
     * @return boolean Returns true on success or false on failure.
     */
    public function offsetExists($offset)
    {
        return isset($this->registry[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     * @return callable Can return all value types.
     */
    public function offsetGet($offset)
    {
        return isset($this->registry[$offset])
               ? $this->registry[$offset]
               : NULL;
    }

    /**
     * Offset to set
     *
     * @param mixed $offset
     * @param callable $value
     */
    public function offsetSet($offset, $value)
    {
        $this->registry[$offset] = $value;
        $this->filtered[$offset] = $this->filter($offset);
   }

    /**
     * Offset to unset
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->registry[$offset]);
        unset($this->filtered[$offset]);
    }
}