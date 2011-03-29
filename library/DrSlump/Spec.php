<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

namespace DrSlump;

/**
 * Static class for Spec functionality
 *
 * @todo With Suhosin patch do we need to add: suhosin.executor.include.whitelist = "spec" ?
 *
 * @package     Spec
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
 */
class Spec
{
    /** Library version */
    const VERSION = '@package_version@';
    /** Scheme used to register the stream wrapper */
    const SCHEME = 'spec';

    /** @var \SplStack */
    protected static $suites = null;

    /** @var Spec\RunHelper */
    protected static $runHelper = null;


    /**
     * Initializes the Spec library
     *
     * @static
     * @param bool $autoload    Unless it's false it will register a custom autoloader
     * @return bool
     */
    public static function init($autoload = true)
    {
        static $alreadyInitialized = false;

        if ($alreadyInitialized) {
            return false;
        }

        // Register auto loader
        if ($autoload) {
            self::autoload();
        }

        // Register stream wrapper for spec files
        if (!in_array(self::SCHEME, stream_get_wrappers())) {
            stream_wrapper_register(
                self::SCHEME,
                __NAMESPACE__ . '\Spec\StreamWrapper'
            );
        }

        // Black list Spec files for PHPUnit
        if (class_exists('\PHP_CodeCoverage_Filter', false)) {
            $filter = \PHP_CodeCoverage_Filter::getInstance();

            $filter->addFileToBlacklist(__FILE__, 'PHPUNIT');
            $filter->addDirectoryToBlacklist(
                __DIR__ . DIRECTORY_SEPARATOR . 'Spec',
                '.php', '', 'PHPUNIT', FALSE
            );

            // Also add Hamcrest matcher library from the include path
            $paths = explode(PATH_SEPARATOR, get_include_path());
            foreach ($paths as $path) {
                if (is_file($path . DIRECTORY_SEPARATOR . 'Hamcrest/MatcherAssert.php')) {
                    $filter->addDirectoryToBlacklist(
                        $path . DIRECTORY_SEPARATOR . 'Hamcrest', '.php', '', 'PHPUNIT', FALSE
                    );
                    break;
                }
            }
        }

        // include standard matchers and keywords
        include_once __DIR__ . '/Spec/keywords.php';
        include_once __DIR__ . '/Spec/matchers.php';

        // Setup suite stack
        self::$suites = new \SplStack();

        return true;
    }

    /**
     * Register a custom autoloader for the library
     *
     * @static
     * @return bool
     */
    public static function autoload()
    {
        spl_autoload_register(function($class){
            $prefix = __CLASS__ . '\\';
            if (strpos($class, $prefix) === 0) {
                // Remove vendor from name
                $class = substr($class, strlen(__NAMESPACE__)+1);

                // Convert namespace separators to directory ones
                $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
                // Prefix with this file's directory
                $class = __DIR__ . DIRECTORY_SEPARATOR . $class;

                include($class . '.php');
                return true;
            }

            return false;
        });
    }

    /**
     * Obtain (or set) the matcher broker
     *
     * @param \DrSlump\Spec\MatcherBroker $set_broker
     * @return \DrSlump\Spec\MatcherBroker
     */
    public static function matchers($set_broker = NULL)
    {
        static $broker = NULL;

        if (NULL !== $set_broker) {
            $broker = $set_broker;
        }

        if (NULL === $broker) {
            $broker = new \DrSlump\Spec\MatcherBroker();
        }

        return $broker;
    }

    /**
     * Get the current suite
     *
     * @static
     * @return \DrSlump\Spec\TestSuite
     */
    public static function suite()
    {
        if (self::$suites->isEmpty()) {
            return null;
        }
        return self::$suites->top();
    }

    /**
     * Get (or set) the current test case
     *
     * @static
     * @param \DrSlump\Spec\TestCaseInterface $test
     * @return \DrSlump\Spec\TestCaseInterface
     */
    public static function test(Spec\TestCaseInterface $test = NULL)
    {
        static $current;

        if (NULL !== $test) {
            $current = $test;
        }

        return $current;
    }

    /**
     * Resets the suites tree with a new root
     *
     * @static
     * @param \DrSlump\Spec\TestSuite
     */
    public static function reset(Spec\TestSuite $root)
    {
        while (!self::$suites->isEmpty()) {
            self::$suites->pop();
        }

        self::$suites->push($root);
    }

    /**
     * Set the run helper object to use
     *
     * @static
     * @param Spec\RunHelper $helper
     */
    public static function setRunHelper(Spec\RunHelper $helper)
    {
        self::$runHelper = $helper;
    }

    /**
     * Get currently configured run helper instance
     *
     * @static
     * @return Spec\RunHelper|null
     */
    public static function getRunHelper()
    {
        if (!self::$runHelper) {
            self::setRunHelper(new Spec\RunHelper());
        }

        return self::$runHelper;
    }


    /**
     * Creates a test suite and runs its code block
     *
     * @static
     * @param string $title
     * @param closure $cb
     */
    public static function describe($title, $cb)
    {
        // Create a new suite to model the describe
        $suite = new Spec\TestSuite();
        $suite->setTitle($title);
        $suite->setCallback($cb);

        // Link parent suite and the new one
        $parent = self::suite();
        $suite->setParent($parent);

        // Set this suite as the current one
        self::$suites->push($suite);

        // Run the describe block
        $cb($suite);

        // Go back to the previous parent
        self::$suites->pop();

        // Finally add the suite to the parent one
        $parent->addTest($suite, $suite->getGroups());
    }

    /**
     * Creates a test case and registers it in its parent suite
     *
     * @note It does not run its code block!
     *
     * @static
     * @param string $title
     * @param closure $cb
     */
    public static function it($title, $cb)
    {
        $runHelper = self::getRunHelper();

        $suite = self::suite();
        $test = $runHelper->buildTest($suite, $title, $cb);
        $groups = $runHelper->findGroups($test);
        $suite->addTest($test, $groups);
    }

    /**
     * Register a _before_ hook in the current suite
     *
     * @static
     * @param Closure $cb
     */
    public static function before($cb)
    {
        self::suite()->before($cb);
    }

    /**
     * Register a _before_each_ hook in the current suite
     *
     * @static
     * @param Closure $cb
     */
    public static function before_each($cb)
    {
        self::suite()->beforeEach($cb);
    }

    /**
     * Register an _after_ hook in the current suite
     *
     * @static
     * @param Closure $cb
     */
    public static function after($cb)
    {
        self::suite()->after($cb);
    }

    /**
     * Register an _after_each_ hook in the current suite
     *
     * @static
     * @param Closure $cb
     */
    public static function after_each($cb)
    {
        self::suite()->afterEach($cb);
    }

    /**
     * @static
     * @param mixed $value
     * @param bool $implicitAssert
     * @return Spec\ExpectAutoComplete
     */
    public static function expect($value, $implicitAssert = false)
    {
        return new Spec\Expect($value, $implicitAssert);
    }

}


// Initialize the library as soon as this file is included
if (!defined('SPEC_DO_NOT_INIT') || !SPEC_DO_NOT_INIT) {
    Spec::init();
}

