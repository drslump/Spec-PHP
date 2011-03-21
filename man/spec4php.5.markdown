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

## ANNOTATIONS ##

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
[ANNOTATIONS]: #ANNOTATIONS "ANNOTATIONS"
[EXAMPLES]: #EXAMPLES "EXAMPLES"
[COPYRIGHT]: #COPYRIGHT "COPYRIGHT"
[SEE ALSO]: #SEE-ALSO "SEE ALSO"


[spec4php(1)]: spec4php.1.html
[spec4php(3)]: spec4php.3.html
[spec4php(5)]: spec4php.5.html
