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
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class Spec
{
    /** Library version */
    const VERSION = '@package_version@';
    /** Scheme used to register the stream wrapper */
    const SCHEME = 'spec';

    /** @var Spec\PHPUnit\TestSuite[] */
    protected static $suitesStack = array();

    /** @var Closure[] */
    protected static $matchers = array();

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

    public static function currentSuite()
    {
        if (empty(self::$suitesStack)) {
            return null;
        }
        return self::$suitesStack[ count(self::$suitesStack)-1 ];
    }

    public static function pushSuite($suite)
    {
        array_push(self::$suitesStack, $suite);
    }

    public static function popSuite()
    {
        return array_pop(self::$suitesStack);
    }

    public static function reset(Spec\TestSuite $root)
    {
        self::$suitesStack = array($root);
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
        $parent = self::currentSuite();
        $suite->setParent($parent);

        // Set this suite as the current one
        self::pushSuite($suite);

        // Run the describe block
        $cb($suite);

        // Go back to the previous parent
        self::popSuite();

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

        $suite = self::currentSuite();
        $test = $runHelper->buildTest($suite, $title, $cb);
        $groups = $runHelper->findGroups($test);
        $suite->addTest($test, $groups);
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


    public static function before($cb)
    {
        self::currentSuite()->before($cb);
    }

    public static function before_each($cb)
    {
        self::currentSuite()->beforeEach($cb);
    }

    public static function after($cb)
    {
        self::currentSuite()->after($cb);
    }

    public static function after_each($cb)
    {
        self::currentSuite()->afterEach($cb);
    }
}


// Initialize the library as soon as this file is included
if (!defined('SPEC_DO_NOT_INIT') || !SPEC_DO_NOT_INIT) {
    Spec::init();
}

