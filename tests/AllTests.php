<?php

include_once __DIR__ . '/../library/DrSlump/Spec.php';

// By extending this class all specs in the current directory will be run
class AllTests extends \DrSlump\Spec\DirectoryRunnerHelper
{
    static public function suite()
    {
        return parent::suite();
    }
}

