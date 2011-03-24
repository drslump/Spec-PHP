spec4php(5) -- Spec files
================================

## SYNOPSIS

A typical spec file:

    <?php
    describe "Calculator"
        it "should multiply"
            calculator(1, '*', 10) should equal 10
        end
        it "should divide"
            calculator(4, '/', 2) should equal 2
        end
    end

In this example we have grouped (**describe**) two tests (**it**)
where each one has a single assertion (**should**).

## DESCRIPTION

Spec files are ... TBD


## BLOCKS

TBD

## EXPECTATIONS ##


## COORDINATION ##

Complex expectations can be _coordinated_ by using operators `and`, `or` and
`but`. It's important to understand the operator precedence rules before
using them, although they try to follow common conventions for the english
language there might be cases where they don't quite do what they look like.

All operators are left-associative and take two operands, thus the precedence
rules are very simple:

      operator  |  precedence index
    ---------------------------------
        and     |        3
        or      |        2
        but     |        1
        ,and    |        1

Please note that it's not possible to override the standard precedence rules
by using parentheses. Expectations should be kept simple, when in doubt break
up complex expectations into simpler ones.

Please review the following examples to see how these precedence rules
apply.

    should be integer or string and equal "1"
    (integer) OR (string AND equal "1")

    -- Note that a comma followed by an operand behaves like an "or"
    should be integer, float or string
    (integer) OR (float) OR (string)
    should be integer, string and equal to 10 or float
    (integer) OR (string AND equal 10) OR (float)

    -- Note that a comma followed by "and" behaves like a "but"
    should be integer or string but less than 10
    should be integer or string, and less than 10
    (integer OR string) AND (less than 10)

    should be integer or string and equal 0 or float
    (integer) OR (string AND equal 0) OR (float)

    should be integer or string and equal "1" but not be a float
    ( (integer) OR (string AND equal "1") ) AND (not be float)


## ANNOTATIONS ##

Annotations can be defined in two ways, using the standard javadoc like
comment with `@tag` entries or a more lightweight alternative using
a hash line comment followed by a word: `# tag`.

Most annotations are inherited by child `describe` groups and `it`
blocks. In the case where there is a collision the deepest one in
the hierarchy wins.

Spec understands the following annotation tags:

  * `class` <var>class_name</var>:
    Tells Spec to create a test case inherting from the given class.
    This is very useful to allow the use of Spec with custom TestCase
    classes you might already have or for enabling the use of Zend_Test
    or PHPUnit's Selenium test case implementation.

  * `throws` [_code_] <var>class</var> [<var>message</var>]:
    This annotation instructs Spec to perform an additional assertion
    when runnning the test, ensuring that it should throw an exception
    matching the given code or the given exception class.

  * `todo`, `incomplete`:
    Flags a test case as incomplete. Spec will report these test cases
    in a different way to standard ones, so it's easy to know when a
    test is passing but doesn't yet tests all the funcionality it should.

  * `skip`:
    A test case with this tag will make Spec skip its execution but log
    in the report that it was skipped. It's a great way to disable some
    test cases known to fail for any reason.

Additionally, most PHPUnit annotations should work when using spec files
too, see [](http://www.phpunit.de/manual/current/en/appendixes.annotations.html)


## EXAMPLES ##


## COPYRIGHT ##

Spec for PHP is Copyright (C) 2011 Ivan -DrSlump- Montes <http://pollinimini.net>


## SEE ALSO

spec4php(1), spec4php(3),
<http://github.com/drslump/spec-php>


[SYNOPSIS]: #SYNOPSIS "SYNOPSIS"
[DESCRIPTION]: #DESCRIPTION "DESCRIPTION"
[BLOCKS]: #BLOCKS "BLOCKS"
[EXPECTATIONS]: #EXPECTATIONS "EXPECTATIONS"
[COORDINATION]: #COORDINATION "COORDINATION"
[ANNOTATIONS]: #ANNOTATIONS "ANNOTATIONS"
[EXAMPLES]: #EXAMPLES "EXAMPLES"
[COPYRIGHT]: #COPYRIGHT "COPYRIGHT"
[SEE ALSO]: #SEE-ALSO "SEE ALSO"


[spec4php(1)]: spec4php.1.html
[spec4php(3)]: spec4php.3.html
[spec4php(5)]: spec4php.5.html
[ronn]: http://rtomayko.github.com/ronn
[phpunit]: http://phpunit.de
