<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

namespace DrSlump\Spec\Matcher;

/**
 * Is the value falsy? Casts the value to bool and checks for true
 */
class Falsy extends \Hamcrest_BaseMatcher
{
    public function matches($arg)
    {
        return $arg == false;
    }

    public function describeTo(\Hamcrest_Description $description)
    {
        $description->appendText('falsy');
    }

    public static function falsyValue()
    {
        return new self();
    }
}


