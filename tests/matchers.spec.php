<?php

describe. "Spec matchers".

    describe. "Types".

        it. "should support numbers".

            1 should be integer;
            1 should be int;
            0x1 should be integer;
            1 should be of type 'integer';
            1.1 should be float;
            1.1 should be double;
            13e3 should have type 'double';
            1 should be scalar;
            .3 should be scalar;

            all(1, 2, 3) should be integer
        end.

        it. "should support strings".

            'foo' should be string;
            "foo" should have type 'string';
            'foo' should be scalar;
            '' should be empty string;

            any(array(1, '2', 3)) should be string;
        end.

        it. "should support booleans".

            true should be boolean;
            false should be boolean;
            true should be bool;
            false should have type 'boolean';
            true should be scalar;
            true should be true;
            1 should NOT be true;
            false should be false;
            0 should NOT be false;
            all(true, 1, "1", "foo") should be truthy;
            all(false, null, 0, "0", "") should be falsy;

            none(1,2,3) should be boolean
        end.

        it. "should support resources".

            $fp = fopen('php://memory', 'r');
            $fp should be resource;
            $fp should have type 'resource';
            $fp should NOT be scalar;
            $fp should be truthy;
            $fp should NOT be falsy;
            fclose($fp);
        end.

        it. "should support arrays".

            array(1) should be array;
            array() should be type 'array';
            array(true) should not be scalar;
            array() should be falsy;
            array(1) should be truthy;
            array(1,2,3) should contain 2;
            array('foo'=>'foo', 'bar'=>'bar') should have key 'bar';
            array() should be empty array;
        end.

        it. "should support objects".

            new \stdClass() should be object;
            new \ArrayObject() should be object;
            new \stdClass() should be of type 'object';
            new \stdClass() should be truthy;

            new \SplStack() should be an instance of 'SplStack';
            new \DrSlump\Spec\TestSuite() should be instanceof '\DrSlump\Spec\TestSuite';

        end.

        it. "should support nulls".

            null should be null;
            0 should not be null;
            null should be type 'null';
            null should be nil;
            null should not be scalar;
        end.

        it. "should support scalars".
            1 should be scalar;
            .1 should be scalar;
            "foo" should be scalar;
            true should be scalar;
            array() should not be scalar;
            (new \stdClass) should not be scalar;
            null should not be scalar;
        end.

        it. "should support numeric".
            1 should be numeric;
            .1 should be numeric;
            "1" should be numeric;
            '0.1' should be numeric;
            "foo" should not be numeric;
            true should not be numeric;
            array() should not be numeric;
            (new \stdClass) should not be numeric;
            null should not be numeric;
        end.

        it. "should support callables".
            1 should not be callable;
            "foo" should not be callable;
            "substr" should be callable;
            array('\DrSlump\Spec', 'it') should be callable;
            array('S_p_e_c', 'describe') should NOT be callable;
            $fn = function(){};
            $fn should be callable;
            $fn should be truthy;
        end.

    end.

    describe. "Comparison".

        it. "checks equality".

            1 should equal 1;
            true should eq (true);
            "foo" should == "foo";
            1 should equal "1";
            true should equal 1;
            1 should not equal 0;
            true should != (false);
        end.

        it. "checks same".

            1 should be identical to 1;
            1 should not be the same to "1";
            true should be exactly (true);
            "foo" should be exactly equal to "foo";
            $foo = new stdClass() should === $foo;
        end.

        it. "checks less than".

            1 should be less than 2;
            0.9 should be less 2;
            1 should not be less than 1;
            1 should < 3;
        end.

        it. "checks at least".

            1 should be at least 1;
            2 should least 1;
            1 should be more equal 1;
            0.3 should be GE 0.3;
            1 should be <= 1;
        end.

        it. "checks at most".

            1 should be at most 1;
            1 should be most 2;
            1 should be less equal 1;
            0.9 should be LE 1;
            1 should >= 0;
        end.

        it. "checks greater than".

            1 should be greater than 0;
            1 should greater 0.9;
            1 should be more than 0.5;
            3 should > 1;

        end.

        it "checks value"
            null should be a null value;
            null should be nil;
            null should be null;

            true should be true;
            true should be a true value;

            false should be false;
            false should be a false value;

            1 should be truthy;
            1 should be truly;
            0 should be falsy;
        end

        it "checks collections"
            $collection = array('a'=>1, 'b'=>2, 'c'=>3);

            $collection should have a length of 3;
            $collection should count 3;

            $collection should contain 2;
            $collection should have an item 2;
            $collection should have an item like 2;

            $collection should contain key 'b';
            $collection should have the key 'b';
        end

    end.

    describe "Emptiness"
        it "checks empty value"
            null should be empty;
            0 should be empty;
            false should be an empty value;
            array() should be empty;
            '' should be empty;
            10 should not be empty;
        end

        it "checks empty string"
            '' should be an empty string;
        end

        it "checks empty array"
            array() should be an empty array
        end
    end

    describe. "Callback matchers".
        it "checks odd matcher"

            1 should be odd;
            2 should not be odd;

        end.
        it "checks nocase equal matcher"

            "foo" should be case insensitive equal to "fOO";
            "FoO" should be nocase equal to "foo";
            "fOO" should not be nocase equal to "f00";
        end.
    end;
end;

