#!/usr/bin/env php
<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero General Public License as
//  published by the Free Software Foundation, either version 3 of the
//  License, or (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero General Public License for more details.
//
//  You should have received a copy of the GNU Affero General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.


// Include this file into CodeCoverage's blacklist
require_once 'PHP/CodeCoverage/Filter.php';
PHP_CodeCoverage_Filter::getInstance()->addFileToBlacklist(__FILE__, 'PHPUNIT');

if (extension_loaded('xdebug')) {
    xdebug_disable();
}

// For non pear packaged versions use relative include path
if (strpos('@php_bin@', '@php_bin') === 0) {
    set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'library' . PATH_SEPARATOR . get_include_path());
}

require_once 'DrSlump/Spec.php';

DrSlump\Spec\Cli::run();
