<?php

namespace DrSlump\Spec\Matcher;

/**
 * Is the value a boolean and true?
 */
class True extends \Hamcrest_BaseMatcher
{
    public function matches($arg)
    {
        return $arg === true;
    }

    public function describeTo(\Hamcrest_Description $description)
    {
        $description->appendText('boolean and true');
    }

    public static function trueValue()
    {
        return new self();
    }
}



