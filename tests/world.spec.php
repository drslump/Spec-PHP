<?php

describe. "World".

    before.
        $W->foo = array('before');
    end;

    before_each.
        $W->foo[] = 'before_each';
        if ($W->foo) {
          //
        }
    end;

    it. "should access initialized variables";
        $W->foo should eq array('before', 'before_each');
        // Modify the value to check if that contaminates further tests
        $W->foo[] = 'it';

        if ($W->foo) {
          //
        }

    end.

    context. "nested suite"

        before.
            $W->foo[] = 'nested_before';
        end;

        before_each.
            $W->foo[] = 'nested_before_each';
        end;

        specify. "should inherit variables from parent suite";
            $W->foo should eq array(
                'before',
                'nested_before',
                'before_each',
                'nested_before_each',
            );
        end.

    end;
end;


