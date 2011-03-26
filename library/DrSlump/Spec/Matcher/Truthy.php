<?php

namespace DrSlump\Spec\Matcher;

/**
 * Is the value truthy. Casts the value to bool and checks for true
 */
class Truthy extends \Hamcrest_BaseMatcher
{
    public function matches($arg)
    {
        return $arg == true;
    }

    public function describeTo(\Hamcrest_Description $description)
    {
        $description->appendText('truthy');
    }

    public static function truthyValue()
    {
        return new self();
    }
}

