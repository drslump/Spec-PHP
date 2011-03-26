<?php

namespace DrSlump\Spec\Matcher;

/**
 * Is the value a boolean and false?
 */
class False extends \Hamcrest_BaseMatcher
{
    public function matches($arg)
    {
        return $arg === false;
    }

    public function describeTo(\Hamcrest_Description $description)
    {
        $description->appendText('boolean and false');
    }

    public static function falseValue()
    {
        return new self();
    }
}



