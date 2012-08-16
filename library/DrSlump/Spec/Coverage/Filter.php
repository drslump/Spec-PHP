<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

namespace DrSlump\Spec\Coverage;

require_once 'PHPUnit/Autoload.php';
require_once 'PHP/CodeCoverage/Filter.php';

/**
 * Extends a PHP CodeCoverage Filter to maintain backward for Spec
 *
 * @package     Spec\Coverage
 * @author      Kenichiro Kishida <sizuhiko@gmail.com>
 *
 * @copyright   Copyright 2012, Kenichiro Kishida
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
 */
class Filter extends \PHP_CodeCoverage_Filter
{
    protected static $filter_instance;

    public static function __callStatic($name, $arguments)
    {
        if($name == 'getInstance') {
            if (self::$filter_instance === NULL) {
                self::$filter_instance = new Filter;
            }
            return self::$filter_instance;
        }
    }
    /**
     * @see PHP_CodeCoverage_Filter::isFiltered()
     */
    public function isFiltered($filename, array $groups = array('DEFAULT'), $ignoreWhitelist = FALSE)
    {
        return parent::isFiltered($filename, $ignoreWhitelist);
    }

    /**
     * @see PHP_CodeCoverage_Filter::addFileToBlacklist()
     */
    public function addFileToBlacklist($filename, $group = 'DEFAULT')
    {
        return parent::addFileToBlacklist($filename);
    }

    /**
     * @see PHP_CodeCoverage_Filter::addDirectoryToBlacklist()
     */
    public function addDirectoryToBlacklist($directory, $suffix = '.php', $prefix = '', $group = 'DEFAULT')
    {
        return parent::addDirectoryToBlacklist($directory, $suffix, $prefix);
    }


}
