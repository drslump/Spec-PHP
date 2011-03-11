<?php
// Implements the StackTest example from PHPUnit's manual as a spec

/** @var $this DrSlump\Spec\PHPUnit\TestCase */

describe. "Testing array operations with Spec".

    it. "should support Push and Pop".

        $stack = array();
        count($stack). should. equal. 0;

        array_push($stack, 'foo');
        $stack[count($stack)-1] SHOULD == 'foo';
        count($stack) SHOULD BE 1;

        array_pop($stack)
            Should be 'foo'

        count($stack)
            should be equal to 0

    end
end
