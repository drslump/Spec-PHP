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

        it. "should support strings".

            'foo' should be string;
            "foo" should have type 'string';

        it. "should support booleans".

            true should be boolean;
            false should be boolean;
            true should be bool;
            false should have type 'boolean';

        it. "should support resources".

            $fp = fopen('php://memory', 'r');
            $fp should be resource;
            fclose($fp);

        it. "should support arrays".

            array(1) should be array;
            array() should be type 'array';

        it. "should support objects".

            new \stdClass() should be object;
            new \ArrayObject() should be object;

        it. "should support nulls".

            null should be null;
            0 should not be null;
            null should be type 'null';


    describe. "Comparison".

        it. "checks equality".

            1 should equal 1;
            true should eq (true);
            "foo" should == "foo";
            1 should equal to "1";
            true should equal 1;
            1 should not equal 0;
            true should != (false);

        it. "checks same".

            1 should be identical to 1;
            1 should not be the same to "1";
            true should be exactly (true);
            "foo" should be exactly equal to "foo";
            $foo = new stdClass() should === $foo;

        it. "checks less than".

            1 should be less than 2;
            0.9 should be less 2;
            1 should not be less than 1;
            1 should < 3;

        it. "checks at least".

            1 should be at least 1;
            2 should least 1;
            1 should be more equal 1;
            0.3 should be GE 0.3;
            1 should be <= 1;

        it. "checks at most".

            1 should be at most 1;
            1 should be most 2;
            1 should be less equal 1;
            0.9 should be LE 1;
            1 should >= 0;

        it. "checks greater than".

            1 should be greater than 0;
            1 should greater 0.9;
            1 should be more than 0.5;
            3 should > 1;

