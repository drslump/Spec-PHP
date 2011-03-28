<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

namespace DrSlump\Spec;

class Expression
{
    protected $parts = array();

    public function reset()
    {
        $this->parts = array();
    }

    public function addOperator($type, $priority = 0)
    {
        $op = new Operator($type, $priority);
        $this->parts[] = $op;
    }

    public function addOperand(\Hamcrest_Matcher $matcher)
    {
        $this->parts[] = $matcher;
    }

    /**
     * Converts the current expression into a single matcher, applying
     * coordination operators to operands according to their binding rules
     *
     * @throws \RuntimeException
     * @return \Hamcrest_Matcher
     */
    public function build()
    {
        // Apply Shunting Yard algorithm to convert the infix expression
        // into Reverse Polish Notation. Since we have a very limited
        // set of operators and binding rules, the implementation becomes
        // really simple
        $ops = new \SplStack();
        $rpn = array();
        foreach ($this->parts as $token) {
            if ($token instanceof Operator) {
                while (!$ops->isEmpty() && $token->compare($ops->top()) <= 0) {
                    $rpn[] = $ops->pop();
                }
                $ops->push($token);
            } else {
                $rpn[] = $token;
            }
        }
        // Append the remaining operators
        while (!$ops->isEmpty()) {
            $rpn[] = $ops->pop();
        }

        // Walk the RPN expression to create AnyOf and AllOf matchers
        $stack = new \splStack();
        foreach ($rpn as $token) {
            if ($token instanceof Operator) {

                // Our operators always need two operands
                if ($stack->count() < 2) {
                    throw new \RuntimeException('Unable to build a valid expression. Not enough operands available.');
                }

                $operands = array(
                    $stack->pop(),
                    $stack->pop(),
                );

                // Check what kind of matcher we need to create
                if ($token->getKeyword() === 'OR') {
                    $matcher = new \Hamcrest_Core_AnyOf($operands);
                } else { // AND, BUT
                    $matcher = new \Hamcrest_Core_AllOf($operands);
                }

                $stack->push($matcher);
            } else {
                $stack[] = $token;
            }
        }

        if ($stack->count() !== 1) {
            throw new \RuntimeException('Unable to build a valid expression. The RPN stack should have just one item.');
        }

        return $stack->pop();
    }

}
