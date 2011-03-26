<?php

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


