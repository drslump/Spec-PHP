<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

namespace DrSlump\Object;

require_once 'Object/Freezer.php';

/**
 * Overrides the original implementation to support dynamic properties
 *
 * @see https://github.com/sebastianbergmann/php-object-freezer/pull/5
 */
class Freezer extends \Object_Freezer
{
    public function thaw(array $frozenObject, $root = NULL, array &$objects = array())
    {
        // Bail out if one of the required classes cannot be found.
        foreach ($frozenObject['objects'] as $object) {
            if (!class_exists($object['className'], $this->useAutoload)) {
                throw new RuntimeException(
                  sprintf(
                    'Class "%s" could not be found.', $object['className']
                  )
                );
            }
        }

        // By default, we thaw the root object and (recursively)
        // its aggregated objects.
        if ($root === NULL) {
            $root = $frozenObject['root'];
        }

        // Thaw object (if it has not been thawed before).
        if (!isset($objects[$root])) {
            $className = $frozenObject['objects'][$root]['className'];
            $state     = $frozenObject['objects'][$root]['state'];

            // Use a trick to create a new object of a class
            // without invoking its constructor.
            $objects[$root] = unserialize(
              sprintf('O:%d:"%s":0:{}', strlen($className), $className)
            );

            // Handle aggregated objects.
            $this->thawArray($state, $frozenObject, $objects);

            $reflector = new \ReflectionObject($objects[$root]);

            foreach ($state as $name => $value) {
                if (strpos($name, '__php_object_freezer') !== 0) {
                    if ($reflector->hasProperty($name)) {
                        $attribute = $reflector->getProperty($name);
                        $attribute->setAccessible(TRUE);
                        $attribute->setValue($objects[$root], $value);
                    } else {
                        $objects[$root]->$name = $value;
                    }
                }
            }

            // Store UUID.
            $objects[$root]->__php_object_freezer_uuid = $root;

            // Store hash.
            if (isset($state['__php_object_freezer_hash'])) {
                $objects[$root]->__php_object_freezer_hash =
                $state['__php_object_freezer_hash'];
            }
        }

        return $objects[$root];
    }
}