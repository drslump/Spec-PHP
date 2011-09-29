<?php
// Implements the StackTest example from PHPUnit's manual as a spec

describe("Testing array operations with Spec", function(){

    it("should support Push and Pop", function(){

        $stack = array();
        expect(count($stack))->equal(0);

        array_push($stack, 'foo');
        expect($stack[count($stack)-1])->equal('foo');
        expect(count($stack))->be(1);

        $pop = array_pop($stack);
        expect($pop)->to_be('foo');

        expect(count($stack))
            ->to_equal(0);

    });

});


