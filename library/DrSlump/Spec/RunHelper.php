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
 * Helper methods to setup and run a test
 *
 * @package     Spec
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
 */
class RunHelper
{
    const DEFAULT_TESTCASE_CLASS = '\PHPUnit_Framework_TestCase';

    /** @var int */
    protected static $evalClassCounter = 0;

    /**
     * Fetches all the annotations for a given suite and test block.
     *
     * @note repeated names are sorted with deepest first
     *
     * @param Spec\TestSuite $suite
     * @param Closure $cb
     * @return array
     */
    protected function getAnnotations(Spec\TestSuite $suite, $cb)
    {
        $anns = array();

        // Get annotations from the callback function first
        $reflFn = new \ReflectionFunction($cb);
        $docblock = $reflFn->getDocComment();

        if (preg_match_all('/@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?(\*\/|$)/m', $docblock, $matches)) {
            $numMatches = count($matches[0]);
            for ($i = 0; $i < $numMatches; ++$i) {
                $anns[ $matches['name'][$i] ][] = trim($matches['value'][$i]);
            }
        }

        // Fetch annotations from parent suites (deepest first)
        do {
            $anns = array_merge_recursive($anns, $suite->getAnnotations());
        } while($suite = $suite->getParent());

        return $anns;
    }


    /**
     * Creates and configures a TestCase object
     *
     * @param Spec\TestSuite $suite
     * @param String $title
     * @param Closure $cb
     * @return \PHPUnit_Framework_TestCase
     */
    public function buildTest(Spec\TestSuite $suite, $title, $cb)
    {
        // Generate a dummy class with eval (sigh) so that PHPUnit use of
        // reflection methods for code coverage and loggers do get some
        // meaningful names.

        // Get all the annotations from this test and parent suites
        $anns = $this->getAnnotations($suite, $cb);

        // Find out if we want to extend a custom TestCase class
        if (!empty($anns['class'])) {
            $extendee = $anns['class'][0];
        } else {
            $extendee = self::DEFAULT_TESTCASE_CLASS;
        }

        // Ensure the extended class is available
        if (!class_exists($extendee, true)) {
            $fname = str_replace(Spec::SCHEME . '://', '', $suite->getFilename());
            throw new \RuntimeException('Unable to find test case class ' . $extendee .
                                        ' for test "' . $title . '" in ' . $fname);
        }

        // Compute meaningful class and method names
        $class = 'describe_' . preg_replace('/[^A-Z0-9_\x7f-\xff]+/i', '_', $suite->getTitle())
                 . '_' . (self::$evalClassCounter++);
        $method = 'it_' . preg_replace('/[^A-Z0-9_\x7f-\xff]+/i', '_', $title);

        // Generate docblock with all the annotations found
        $docblock = "    /**\n";
        foreach ($anns as $name=>$values) {
            foreach ($values as $value) {
                $docblock .= "     * @$name $value\n";
            }
        }
        $docblock .= "     */";


        // Use eval to dynamically create the new class
        $parsed = eval("
            class $class extends $extendee implements \DrSlump\Spec\TestCaseInterface {
                public \$title;
                public \$callback;
                public \$suite;
                public \$annotations;

                function getTitle(){
                    return \$this->title;
                }

                function getSuite(){
                    return \$this->suite;
                }

                function setUp(){
                    parent::setUp();
                    \DrSlump\Spec::getRunHelper()->setUp(\$this);
                }

                function tearDown(){
                    \DrSlump\Spec::getRunHelper()->tearDown(\$this);
                    parent::tearDown();
                }

                function runBare(){
                    \DrSlump\Spec::getRunHelper()->prepareTest(\$this);
                    parent::runBare();
                    \DrSlump\Spec::getRunHelper()->disposeTest(\$this);
                }

                function onNotSuccessfulTest(\$e) {
                    \$e = \DrSlump\Spec::getRunHelper()->onNotSuccessfulTest(\$this, \$e);
                    parent::onNotSuccessfulTest(\$e);
                }

                function __isset(\$prop) {
                    return isset(\$this->\$prop);
                }

                function __get(\$prop) {
                    return isset(\$this->\$prop) ? \$this->\$prop : null;
                }

                function __set(\$prop, \$value) {
                    return \$this->\$prop = \$value;
                }


                $docblock
                function $method(){
                    \$args = func_get_args();
                    \DrSlump\Spec::getRunHelper()->runTest(\$this, \$args);
                }
            }
        ");

        // Check if everything went fine
        if (FALSE === $parsed || !class_exists($class, false)) {
            throw new \RuntimeException(
                'There was a problem creating the test case. Class ' . $class . ' could not be found.'
            );
        }

        // Create a new test case and configure it
        $test = new $class($method);
        $test->title = $title;
        $test->callback = $cb;
        $test->suite = $suite;
        $test->annotations = $anns;

        return $test;
    }

    /**
     * Find groups/tags associated with this test
     *
     * @param Spec\TestCaseInterface $test
     * @return Array
     */
    public function findGroups(Spec\TestCaseInterface $test)
    {
        $anns = $test->annotations;

        $groups = array();
        if (!empty($anns['group'])) {
            $groups = array_merge($groups, $anns['group']);
        }
        if (!empty($anns['tag'])) {
            $groups = array_merge($groups, $anns['tag']);
        }

        return $groups;
    }

    /**
     * Analyzes an exception returned by the test case, modifying it
     * if needed.
     *
     * @param Spec\TestCaseInterface  $test
     * @param \Exception $e
     * @return \Exception
     */
    public function onNotSuccessfulTest(Spec\TestCaseInterface  $test, $e)
    {
        if ($e instanceof \PHPUnit_Framework_IncompleteTest) {
            $exClass = '\DrSlump\Spec\IncompleteTestError';
            $test->status = \PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE;
        } elseif ($e instanceof \PHPUnit_Framework_SkippedTest) {
            $exClass = '\DrSlump\Spec\SkippedTestError';
            $test->status = \PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED;
        } elseif ($e instanceof \PHPUnit_Framework_AssertionFailedError ||
                  $e instanceof \PHPUnit_Framework_ExpectationFailedException ||
                  $e instanceof \Hamcrest_AssertionError) {
            $exClass = '\PHPUnit_Framework_SyntheticError';
            $test->status = \PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
        } else {
            // Unknown exception
            return $e;
        }

        // Normalize stack trace if running thru PHPUnit
        if (defined('PHPUnit_MAIN_METHOD')) {
            $origTrace = $e->getTrace();
            $trace = array();
            $last = null;
            foreach ($origTrace as $frame) {
                if (!empty($frame['file'])) {
                    // Skip repeated entries
                    if ($last && $last['file'] === $frame['file'] && $last['line'] === $frame['line']) {
                        continue;
                    }

                    $last = $frame;

                    // Remove stream wrapper prefix (spec://)
                    if (0 === strpos($frame['file'], Spec::SCHEME . '://')) {
                        $frame['file'] = substr($frame['file'], strlen(Spec::SCHEME) + 3);
                    }
                }

                $trace[] = $frame;
            }
        } else {
            $trace = $e->getTrace();
        }

        $e = new $exClass(
                $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $trace
        );

        return $e;
    }

    /**
     * Prepares a test to be run. This method executes before setUp.
     *
     * @param Spec\TestCaseInterface  $test
     */
    public function prepareTest(Spec\TestCaseInterface  $test)
    {
        $ann = $test->annotations;

        /* @todo Shouldn't this be already done by PHPUnit
        if (!empty($ann['outputBuffering'])) {
            $enabled = filter_var($ann['outputBuffering'][0], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $test->setUseOutputBuffering($enabled);
        }

        if (!empty($ann['errorHandler'])) {
            $enabled = filter_var($ann['errorHandler'][0], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $test->setUseErrorHandler($enabled);
        }
        */

        // Check @throws annotation
        // @todo can this be moved to setUp so it overrides PHPUnit annotations?
        if (!empty($ann['throws'])) {
            $regexp = '/([:.\w\\\\x7f-\xff]+)(?:[\t ]+(\S*))?(?:[\t ]+(\S*))?\s*$/';
            if (preg_match($regexp, $ann['throws'][0], $m)) {
                $class = $code = $message = null;
                if (is_numeric($m[1])) {
                    $code = $m[1];
                } else {
                    $class = $m[1];
                }
                $message = isset($m[2]) ? $m[2] : null;
                $code = isset($m[3]) ? (int)$m[3] : $code;
                $test->setExpectedException($class, $message, $code);
            }
        }

        // Register the current test case as the active one
        Spec::test($test);
    }

    /**
     * Clean up a ran test. This method executes after tearDown.
     *
     * @param Spec\TestCaseInterface  $test
     * @return void
     */
    public function disposeTest(\PHPUnit_Framework_TestCase $test)
    {
    }

    /**
     * The standard setUp method invocation
     *
     * @param Spec\TestCaseInterface  $test
     */
    public function setUp(Spec\TestCaseInterface  $test)
    {
        $suite = $test->getSuite();

        // Create a snapshot of the world
        $suite->createWorldSnapshot();

        // Run before each callbacks
        // @todo Move callback invocation logic here
        $suite->runBeforeEachCallbacks($test);

        if (class_exists('\Hamcrest_MatcherAssert', true)) {
            $test->hamcrestAssertCount = \Hamcrest_MatcherAssert::getCount();
        }
    }

    /**
     * The standard tearDown method invocation
     *
     * @param Spec\TestCaseInterface $test
     */
    public function tearDown(Spec\TestCaseInterface $test)
    {
        if (class_exists('\Hamcrest_MatcherAssert', true)) {
            $test->addToAssertionCount(\Hamcrest_MatcherAssert::getCount() - $test->hamcrestAssertCount);
        }

        $suite = $test->getSuite();

        // Restore the world
        $suite->restoreWorldSnapshot();


        // @todo Move callback invocation logic here
        $suite->runAfterEachCallbacks($test);
    }

    /**
     * Executes the test block.
     *
     * @param Spec\TestCaseInterface  $test
     * @param array $args
     */
    public function runTest(Spec\TestCaseInterface  $test, array $args = array())
    {
        // Check for skip annotations
        $ann = $test->annotations;
        if (isset($ann['skip'])) {
            $test->markTestSkipped(isset($ann['skip'][0]) ? $ann['skip'][0] : '');
        }

        // First param is always the current test object
        $params = array($test->getSuite()->getWorld());

        // Extract values from parametrized titles
        preg_match_all('/([\'"<])(.*?)(\1|>)/', $test->getTitle(), $m);
        $params = array_merge($params, $m[2]);

        // Add any arguments given when calling the test (@depends, @dataProvider)
        $params = array_merge($params, $args);

        // Finally, execute the test block
        call_user_func_array($test->callback, $params);

        // Check for incomplete annotations
        if (isset($ann['todo'])) {
            $test->markTestIncomplete(isset($ann['todo'][0]) ? $ann['todo'][0] : '');
        } elseif (isset($ann['incomplete'])) {
            $test->markTestIncomplete(isset($ann['incomplete'][0]) ? $ann['incomplete'][0] : '');
        }
    }
}