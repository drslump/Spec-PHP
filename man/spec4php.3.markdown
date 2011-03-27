spec4php(3) -- The framework
============================

## SYNOPSIS

    <?php
    require_once 'DrSlump\Spec.php';

    use DrSlump\Spec;

    Spec::describe('Calculator', function($world){
        Spec::it('should multiply', function($world){
            $result = calculator(1, '*', 3);
            expect($result)->to_be_equal_to(3);
        });
    });


## DESCRIPTION

The Spec framework sits on top of PHPUnit, delegating to it the job of
actually running the tests. This is accomplished with two separate components,
the first is in charge of transforming custom Spec syntax to valid PHP source
code compatible with PHPUnit. The second is a an assertion manager that takes
care of running the expectations following a subject-predicate approach.


## EXAMPLES ##


## COPYRIGHT ##

Spec for PHP is Copyright (C) 2011 Ivan -DrSlump- Montes <http://pollinimini.net>


## SEE ALSO

spec4php(1), spec4php(5),
<http://github.com/drslump/spec-php>


[SYNOPSIS]: #SYNOPSIS "SYNOPSIS"
[DESCRIPTION]: #DESCRIPTION "DESCRIPTION"
[EXAMPLES]: #EXAMPLES "EXAMPLES"
[COPYRIGHT]: #COPYRIGHT "COPYRIGHT"
[SEE ALSO]: #SEE-ALSO "SEE ALSO"


[spec4php(1)]: spec4php.1.html
[spec4php(3)]: spec4php.3.html
[spec4php(5)]: spec4php.5.html
[ronn]: http://rtomayko.github.com/ronn
[phpunit]: http://phpunit.de
