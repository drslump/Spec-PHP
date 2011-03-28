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
 * Defines interface for test cases ran thru Spec
 *
 * @package     Spec
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
 */
interface TestCaseInterface extends \PHPUnit_Framework_Test, \PHPUnit_Framework_SelfDescribing
{
    /**
     * @abstract
     * @return String
     */
    public function getTitle();

    /**
     * @abstract
     * @return \DrSlump\Spec\TestSuite
     */
    public function getSuite();

}
