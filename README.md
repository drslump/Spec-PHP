# Spec for PHP

Spec for PHP is a tool to implement Behaviour-Driven Development specification
files. It's inspired on [RSpec](http://relishapp.com/rspec) from the Ruby world.

It builds on top of [PHPUnit](http://www.phpunit.de) and [Hamcrest](http://code.google.com/p/hamcrest/)
projects to offer mature and solid functionality and borrow the current
industry support for those projects.

The intent when implementing Spec was to mix normal PHP code and a more
natural language syntax to express expectations. Writing the test code
in pure PHP is great because that is how you are going to use it in your
application and IDE's offer autocompletion for it. However, assertions
are difficult to express and difficult to read with PHP's syntax, thus
it makes sense to write them differently.

To be able to parse Spec's syntax there is an on the fly transformation
that generates valid PHP code. Code generation has its drawbacks, specially
when there is a need to debug a problem, the source file and the executed
one are different so it becomes a nighmare to know what's going on. Spec
takes this into account and will try as hard as possible to generate code
that keeps the original line numbers the same. This is of great help since
you can go to the line number reported in an exception and actually see the
statement that failed. Check the "How does it work?" section for more
details.

## Features

_Spec for PHP_ is being actively developed and while functional doesn't
have all of its features implemented. As thus it should be considered
_alpha_ quality software for the time being.

### Working features

 - *Describe* and *It* block parsers
 - Natural language expectations parser
 - Non-combined (and, or, but) expectations
 - Run expectations against collections/arrays (all, any, none)
 - PHPUnit integration
 - Annotations support (@group, @skip, @todo, @throw, ...)

### Upcomming

 - Combined expectations
 - *Before* and *After* blocks
 - Custom CLI runner tool
 - Review configurable options and extension points
 - Improve annotations support

### Later

 - Parametrized *It* blocks
 - Additional matchers
 - Better descriptions for expectation failures
 - Port features from RSpec 2
 - First class integration of Mock frameworks (PHPUnit, Mockery, ...)


## Requirements

 - PHP 5.3
 - PHPUnit 3 (only tested with 3.5)
 - Hamcrest matchers library


## Installation

Install a recent version of PHPUnit

    pear channel-discover pear.phpunit.de
    pear install phpunit/PHPUnit

Install Hamcrest matchers library

    pear channel-discover hamcrest.googlecode.com/svn/pear
    pear install hamcrest/Hamcrest

Checkout a copy of _Spec for PHP_ in your computer and do a test run:

    cd tests/
    phpunit AllTests.php


## Example

    <?php
    // Implements the StackTest example from PHPUnit manual as a spec file
    describe "Testing array operations with Spec"

        it "should support Push and Pop"

            $stack = array();
            count($stack) should equal 0;

            array_push($stack, 'foo');
            $stack[count($stack)-1] SHOULD == 'foo';
            count($stack) SHOULD BE 1;

            array_pop($stack)
            Should be "foo"

            count($stack)
                should be equal to 0
    end

That's it, really, no `->assert` calls and no classes, methods or other
verbose statements just to fit the code in.

The syntax for blocks is borrowed from [RSpec](http://relishapp.com/rspec),
`describe` groups tests and `it` defines a block of code used to execute a
test for specific functionality. Additionally it keeps track of indentation
levels (ala Python) to close blocks automatically, whithout needing to use
the `end` keyword or braces.

Note though that this example uses the most _natural language_ syntax
possible with Spec. It could also be writen with closures or using dots
instead of spaces to separate statements, so that it becomes completely
valid PHP syntax. Check the following example:

    <?php
    describe. "Block without closure".
        it("should multiply", function(){
            (2*2) . should.equal(4);
        });
    end;

If you still preffer how a traditional PHPUnit test case looks, you
might still find this library useful, have a look on the section about
PHPUnit compatibility.


## How does it work?

The parser for the custom syntax uses PHP's own tokenizer (`token_get_all`)
to ensure it doesn't choke on weird statements. When it reaches a block
level keyword like `describe` it wraps the contents in a closure function.

The keyword `should` uses a different parsing logic. The statement just
before it is captured as the "value" or "subject" while statemens after it
are captured as the "expectation" or "predicate". It's even able to parse
_complex_ expressions if they are wrapped in parenthesis.

To fool the PHP interpreter so that it receives the transformed code instead
of the original Spec syntax, a custom stream wrapper is used to perform
the transformation. Every file using the `spec://path/to/file.php` notation
will be run thru Spec's parser to transform it if needed.

In order to make some sense of the expectations, the Expect class
applies some simple algorithms like removing common words or managing
sentence combinators like _and_. Take for example the following sentence:

    5 should be an integer and less than 10

would be processed as if it was:

    expect(5)->integer()->and_less(10);


## Compatibility with PHPUnit

One of the design principles when developing Spec was to make it
compatible with PHPUnit, since it's the current standard tool for 
testing in the PHP world.

Spec files are _transformed_ on the fly to be compatible with PHPUnit,
allowing to use its reporting (code coverage, xunit, tap logs) and 
current integrations with IDEs and Continuous integration services.

Feeding a `\DrSlump\Spec\PHPUnit\TestSuite` object to PHPUnit it will
be able to execute any spec files it references. For example, by
creating the following empty class it will fetch recursively all spec
files in the working directory.

    require_once 'Spec.php';

    class AllSpecs extends \DrSlump\Spec\PHPUnit\DirectorySpecTests {
    }

It's even possible to use the `expect` component in PHPUnit's _native_
test cases.

    class ExpectTest extends PHPUnit_Framework_TestCase {
        function testEqual(){
            expect(1)->to_equal(1);
        }
    }
