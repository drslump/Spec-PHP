spec4php(1) -- cli runner
=========================

## SYNOPSIS

    spec4php <var>FILE</var>
    spec4php <var>DIRECTORY</var>


## DESCRIPTION

**Spec4php** runs the specified spec files and reports the results
in the terminal. For information on how to create spec files please
refer to spec4php(5).


## SPEC FILES

The `spec4php` command expects input files to contain PHP code with
**describe** and **it** blocks in it.

Giving a directory as argument, `spec4php` will run all the files ending
with `.spec.php` or `Spec.php` it finds below that directory.


## OPTIONS ##

The following options (switches) are supported.

  * `-h`, `--help`:
    Prints the usage help message and quits.

  * `-v`, `--verbose`:
    Enables verbose mode. Additional information will be printed.

  * `--debug`:
    Enables debug mode. Stack traces will be shown unfiltered.

  * `--color`=[_auto_|_yes_|_no_]:
    Tells Spec if it should use colors when printing messages. By default
    (if the option is not set) it will use the _auto_ mode, which will try
    to detect if it's safe to use ansi color sequences.

  * `-f` _regexp_, `--filter`=_regexp_:
    Runs only the tests matching the given regular expression. You can
    specify this option multiple times to build complex expressions.

  * `-g` _group_, `--group`=_group_:
    Runs only the tests assigned to the given group or groups. Use commas
    to define multiple groups or specify this option multiple times.

  * `--exclude-group`=_group_:
    Do not run the tests assigned to the given group or groups. Use commas
    to define multiple groups or specify this option multiple times.

  * `--list-group`=_group_:
    Print a list of the available group names.

  * `--format`=_format_:
    The formatter to use to present the result of the tests. The default
    formatter is <var>dots</var>, the other supported formatter is <var>story</var>.

  * `-s`:
    An alias for `--format story`

  * `-b`, `--beep`:
    Turn on beep-on-failures, which will issue a beep sound for each failed
    test.

  * `-d` <var>file</var>, `--dump` <var>file</var>:
    Outputs the generated PHP code for a Spec file. If verbose mode is enabled
    it will print line numbers too.


## EXAMPLES ##


## BUGS ##

Please report bugs via [GitHub's issue tracker](http://github.com/drslump/spec-php/issues)


## COPYRIGHT ##

Spec for PHP is Copyright (C) 2011 Ivan -DrSlump- Montes <http://pollinimini.net>


## SEE ALSO

spec4php(3), spec4php(5),
<http://github.com/drslump/spec-php>


[SYNOPSIS]: #SYNOPSIS "SYNOPSIS"
[DESCRIPTION]: #DESCRIPTION "DESCRIPTION"
[SPEC FILES]: #SPEC-FILES "SPEC FILES"
[OPTIONS]: #OPTIONS "OPTIONS"
[EXAMPLES]: #EXAMPLES "EXAMPLES"
[BUGS]: #BUGS "BUGS"
[COPYRIGHT]: #COPYRIGHT "COPYRIGHT"
[SEE ALSO]: #SEE-ALSO "SEE ALSO"


[spec4php(1)]: spec4php.1.html
[spec4php(3)]: spec4php.3.html
[spec4php(5)]: spec4php.5.html
[ronn]: http://rtomayko.github.com/ronn
[phpunit]: http://phpunit.de
