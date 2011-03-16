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

use \DrSlump\Spec;

/**
 * Extends a PHPUnit Test Suite to adapt it for Spec
 *
 * @package     Spec
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class TestSuite extends \PHPUnit_Framework_TestSuite
{
    /** @var String */
    protected $title;
    /** @var TestSuite */
    protected $parent = NULL;
    /** @var Closure */
    protected $callback;
    /** @var Array */
    protected $annotations = array();
    /** @var String */
    protected $filename = NULL;

    /** @var closure[] */
    protected $beforeCallbacks = array();
    /** @var closure[] */
    protected $beforeEachCallbacks = array();
    /** @var closure[] */
    protected $afterCallbacks = array();
    /** @var closure[] */
    protected $afterEachCallbacks = array();

    /**
     * Set describe block title
     *
     * @param String $title
     */
    public function setTitle($title)
    {
         $this->title = $title;
    }

    /**
     * Get describe block title
     *
     * @return String
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set parent describe block
     *
     * @param TestSuite $suite
     */
    public function setParent(TestSuite $suite)
    {
        $this->parent = $suite;
    }

    /**
     * Get parent describe block
     *
     * @return TestSuite
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set filename that contains this block
     *
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Get filename that contains this block
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }
    /**
     * Sets the callback that will create this suite
     *
     * @param Closure $cb
     */
    public function setCallback($cb)
    {
        $this->callback = $cb;

        // Collect callback annotation
        $reflFunc = new \ReflectionFunction($cb);
        $docblock = $reflFunc->getDocComment();

        $this->annotations = array();
        if (preg_match_all('/@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?(\*\/|$)/m', $docblock, $matches)) {
            $numMatches = count($matches[0]);

            for ($i = 0; $i < $numMatches; ++$i) {
                $this->annotations[$matches['name'][$i]][] = trim($matches['value'][$i]);
            }
        }

        // Get the filename that contains this suite
        $this->setFilename($reflFunc->getFileName());
    }



    public function before($cb)
    {
        $this->beforeCallbacks[] = $cb;
    }

    public function beforeEach($cb)
    {
        $this->beforeEachCallbacks[] = $cb;
    }

    public function after($cb)
    {
        $this->afterCallbacks[] = $cb;
    }

    public function afterEach($cb)
    {
        $this->afterEachCallbacks[] = $cb;
    }


    public function getBeforeEachCallbacks()
    {
        return $this->beforeEachCallbacks;
    }

    public function runBeforeEachCallbacks($test)
    {
        $callbacks = array();

        // Navigate to the root suite collecting callbacks
        $suite = $this;
        do {
            // Append the current suite callbacks in reverse order
            $callbacks = array_merge(
                $callbacks,
                array_reverse($suite->getBeforeEachCallbacks())
            );
        } while($suite = $suite->getParent());

        // Reverse the callbacks to execute from top to bottom
        $callbacks = array_reverse($callbacks);

        // Finally run all the callbacks in order
        foreach ($callbacks as $cb) {
            $cb($test);
        }
    }

    public function getAfterEachCallbacks()
    {
        return $this->afterEachCallbacks;
    }

    public function runAfterEachCallbacks($test)
    {
        $callbacks = array();

        // Navigate to the root suite collecting callbacks
        $suite = $this;
        do {
            // Append the current suite callbacks in reverse order
            $callbacks = array_merge(
                $callbacks,
                array_reverse($suite->getAfterEachCallbacks())
            );
        } while ($suite = $suite->getParent());

        // Reverse the callbacks to sort them top to bottom
        $callbacks = array_reverse($callbacks);

        // Finally run all of them
        foreach ($callbacks as $cb) {
            $cb($test);
        }
    }


    /**
     * Override setUp to execute the before hooks
     */
    public function setUp()
    {
        foreach ($this->beforeCallbacks as $cb) {
            $cb($this);
        }
    }

    /**
     * Override tearDown to execute the after hooks
     */
    public function tearDown()
    {
        foreach ($this->afterCallbacks as $cb) {
            $cb($this);
        }
    }


    /**
     * Returns the annotations for this suite
     *
     * @return array
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }


    /**
     * Wraps both <code>addTest()</code> and <code>addTestSuite</code>
     * as well as the separate import statements for the user's convenience.
     *
     * If the named file cannot be read or there are no new tests that can be
     * added, a <code>PHPUnit_Framework_Warning</code> will be created instead,
     * leaving the current test run untouched.
     *
     * @param  string  $filename
     * @param  boolean $syntaxCheck
     * @param  array   $phptOptions Array with ini settings for the php instance
     *                              run, key being the name if the setting,
     *                              value the ini value.
     * @throws InvalidArgumentException
     */
    public function addTestFile($filename, $syntaxCheck = FALSE, $phptOptions = array())
    {
        if (!is_string($filename)) {
            throw \PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        // Ensure we can read the file
        if (!$filename || !is_readable($filename)) {
            throw new \RuntimeException(
                sprintf('Cannot open file "%s".' . "\n", $filename)
            );
        }

        // Try to convert it to a relative path
        if (strpos($filename, getcwd()) === 0) {
            $filename = substr($filename, strlen(getcwd()) + 1);
        }

        // Use stream wrapper for spec files
        $furl = Spec::SCHEME . '://' . $filename;

        // Setup the environment to collect tests
        \DrSlump\Spec::reset($this);

        \PHPUnit_Util_Fileloader::load($furl);

        $this->numTests = -1;
    }

    /**
     * Returns a string representation of the test suite.
     *
     * @return string
     */
    public function toString()
    {
        return $this->getTitle();
    }

    public function getName()
    {
        return $this->getTitle();
    }
}
