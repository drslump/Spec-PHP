#!/usr/bin/env php
<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/


// Include this file into CodeCoverage's blacklist
require_once 'PHPUnit/Autoload.php';

if (extension_loaded('xdebug')) {
    xdebug_disable();
}

// For non pear packaged versions use relative include path
if (strpos('@php_bin@', '@php_bin') === 0) {
    set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'library' . PATH_SEPARATOR . get_include_path());
}

require_once 'DrSlump/Spec/Coverage/Filter.php';
$filter = DrSlump\Spec\Coverage\Filter::getInstance();
$filter->addFileToBlacklist(__FILE__, 'PHPUNIT');

require_once 'DrSlump/Spec.php';

DrSlump\Spec\Cli::run();
