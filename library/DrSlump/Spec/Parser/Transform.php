<?php
//  Spec for PHP
//  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
//
//  This source file is subject to the MIT license that is bundled
//  with this package in the file LICENSE.
//  It is also available through the world-wide-web at this URL:
//  http://creativecommons.org/licenses/MIT/

namespace DrSlump\Spec\Parser;

/**
 * Generates PHP code transforming the original source of a TokenIterator
 *
 * @package     Spec\Parser
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     http://creativecommons.org/licenses/MIT     The MIT License
 */
class Transform {
    // Define parser states
    const TOP = 'Top';
    const PHP = 'Php';
    const BLOCK = 'Block';
    const STATEMENT = 'Statement';
    const SHOULD = 'Should';
    const PARAM = 'Param';

    // Fully qualified namespace for Spec class
    const SPEC_CLASS = '\DrSlump\Spec';

    // Define comparison operators
    protected $comparisonOps = array(
        '==='   => 'same',
        '!=='   => 'not_same',
        '=='    => 'equal',
        '!='    => 'not_equal',
        '>'     => 'greater',
        '<'     => 'less',
        '>='    => 'at_least',
        '<='    => 'at_most',
    );

    /** @var \Iterator */
    protected $it;

    /** @var int */
    protected $state = self::TOP;

    /** @var string */
    protected $target = '';

    /** @var \SplStack */
    protected $blocks;

    /** @var array */
    protected $nestedBlocks = array();

    /** @var \SplQueue */
    protected $statement;

    /** @var bool */
    protected $inBlock = false;

    // Indentation auto-close
    protected $endAuto = false;
    protected $isIndent = true;
    protected $indent = 0;

    public function __construct(\Iterator $it)
    {
        $this->it = $it;

        $this->statement = new \SplQueue();
        $this->statement->setIteratorMode(\SplDoublyLinkedList::IT_MODE_DELETE);

        $this->blocks = new \SplStack();
    }

    static public function transform(\Iterator $it)
    {
        $obj = new self($it);
        return $obj->generate();
    }

    public function transition($newState)
    {
        $this->state = $newState;
    }

    public function generate()
    {
        try {
            $token = $this->consume(true);
            while ($this->it->valid()) {

                do {
                    $method = 'state' . $this->state;
                    $token = $this->$method($token);
                } while ($token);

                $token = $this->consume();
            }

        } catch (EofException $e) {
            // Reaching the end of file is normal but we shouldn't have any
            // blocks open at this point
            $unclosed = count($this->nestedBlocks);
            if ($unclosed > 0) {
                var_dump($this->nestedBlocks);
                throw new Exception(
                    "The end of file was reached but $unclosed blocks were found still open. " .
                    "Please make sure you've used the 'end' keyword for all blocks"
                );
            }

            //$this->indent = 0;
            //$this->closeIndentedBlocks();
        }

        return $this->target;
    }

    public function closeIndentedBlocks()
    {
        // Close based on indentation
        do {
            list($lastBlock, $auto) = $this->popBlock();
            if ($lastBlock === NULL) {
                // No more blocks to checks
                break;
            } else if (!$auto || $lastBlock < $this->indent) {
                $this->pushBlock($lastBlock, $auto);
                break;
            } else if ($lastBlock !== NULL && $lastBlock >= $this->indent) {
                $this->write(
                    str_repeat(' ', max(0, $lastBlock-4)) . '});' . PHP_EOL . str_repeat(' ', $this->indent),
                    true
                );
            }
        } while ($lastBlock);
    }

    public function consume($rewind = false)
    {
        $rewind ? $this->it->rewind() : $this->it->next();
        if (!$this->it->valid()) {
            throw new EofException();
        }
        $token = $this->it->current();

        // Manage indentation
        if ($this->isIndent && $token->type === Token::WHITESPACE) {
            $this->indent += strlen($token->value);
        } else if ($token->type === Token::EOL) {
            $this->isIndent = true;
            $this->indent = 0;
        } else {
            $this->isIndent = false;
        }

        return $token;
    }

    public function skip($type)
    {
        $types = func_get_args();
        do {
            $token = $this->consume();
            if (!in_array($token->type, $types)) {
                return $token;
            }
        } while($this->it->valid());
    }

    public function write($value, $compact = false)
    {
        if ($compact) {
            $value = preg_replace('/\s\s+/', ' ', $value);
            $value = trim($value);
        }

        $this->target .= $value;
    }

    public function popBlock()
    {
        return array_pop($this->nestedBlocks);
    }

    public function pushBlock($indent, $auto = null)
    {
        if ($auto === NULL) $auto = $this->endAuto;
        array_push($this->nestedBlocks, array($indent, $auto));
    }


    public function appendStatement(Token $token)
    {
        $this->statement->push($token);
    }

    public function dumpStatement()
    {
        foreach ($this->statement as $token) {
            $this->write($token->value);
        }
    }


    public function stateTop(Token $token)
    {
        switch ($token->token) {
        case T_OPEN_TAG:
            $this->transition(self::PHP);
            $this->write($token->value);
            return false;

        default:
            $this->write($token->value);
            return false;
        }
    }

    public function statePhp(Token $token)
    {
        switch ($token->type) {

        case Token::WHITESPACE:
        case Token::EOL:
            $this->appendStatement($token);
            return false;

        case Token::COMMENT:
            $value = trim($token->value);

            // Line comments include a new line character
            if (substr($value, 0, 1) === '#' || substr($value, 0, 2) === '//') {

                // Manage annotations
                if (preg_match('/^#\s*[A-Z-]+/i', $value, $m)) {
                    $value = '/** @' . trim($value, ' #') . ' */' . "\n";
                } else {
                    $value = '/** ' . trim(substr($value, 2)) . ' */' . "\n";
                }
                $value .= str_repeat(' ', max(0, $this->indent-4));

                // Merge consecutive single line comments
                $prevs = array();
                while (!$this->statement->isEmpty()) {
                    $prevs[] = $prev = $this->statement->pop();
                    if ($prev->type === Token::COMMENT) {
                        $prev->value = str_replace('*/', '', $prev->value);
                        $value = substr($value, 3);
                        break;
                    } else if ($prev->type !== Token::WHITESPACE && $prev->type !== Token::EOL) {
                        break;
                    }
                }

                while (count($prevs)) {
                    $this->statement->push( array_pop($prevs) );
                }

                $token->value = $value;

                // Single line comments include a new line
                $this->isIndent = true;
                $this->indent = 0;
            }

            // Check if we want to modify indentation control
            if (stripos($token->value, '@end-manual') !== FALSE) {
                $this->endAuto = false;
            } else if (strpos($token->value, '@end-auto') !== FALSE) {
                $this->endAuto = true;
            }

            $this->appendStatement($token);
            return false;

        case Token::IDENT:
        case Token::DESCRIBE: // todo Deprecate this type
        case Token::IT:       // todo Deprecate this type
        case Token::END:      // todo Deprecate this type
            $ident = strtolower($token->value);
            if (in_array($ident, array('describe', 'it', 'before', 'before_each', 'after', 'after_each', 'end'))) {
                $this->dumpStatement();
                $this->transition(self::BLOCK);
                return $token;
            }

        default:
            if ($token->token === T_CLOSE_TAG) {
                $this->transition(self::TOP);
                return false;
            }

            $this->transition(self::STATEMENT);
            return $token;
        }
    }

    public function stateBlock(Token $token)
    {
        $value = strtolower($token->value);
        $hasMessage = false;

        switch ($value) {
        case 'describe':
        case 'it':
            $hasMessage = true;

        case 'before':
        case 'before_each':
        case 'after':
        case 'after_each':


            $next = $this->skip(Token::WHITESPACE, Token::DOT);
            if ($next->type === Token::LPAREN) {
                $this->appendStatement($token);
                $this->appendStatement($next);
                $this->transition(self::PHP);
                return false;
            }

            $this->dumpStatement();

            $this->closeIndentedBlocks();

            $this->blocks->push($value);
            $this->inBlock = $value !== 'describe';

            $this->write(self::SPEC_CLASS . '::' . $value . '(');

            $args = array('$W');
            if ($hasMessage && $next->type !== Token::QUOTED) {
                throw new Exception('Expected quoted string at line ' . $token->line);
            } else if ($hasMessage) {
                $this->write($next->value);
                $this->write(', ');

                // Count "placeholders" in the message
                $msg = substr($next->value, 1, -1);
                preg_match_all('/([\'"<])[^\s]+(\1|>)/', $msg, $m);
                for ($i=1; $i<=count($m[0]); $i++) {
                    $args[] = '$arg' . $i;
                }
            }

            $this->write('function(');
            $this->write(implode(', ', $args));
            $this->write('){');

            $this->pushBlock($this->indent);
            $this->transition(self::PHP);

            if ($next->type !== Token::EOL) {
                $next = $this->skip(Token::WHITESPACE, Token::DOT, Token::SEMICOLON, Token::COLON);
                if ($next->type !== Token::EOL) {
                    throw new Exception('Expected EOL but found "' . $next->value . '" at line ' . $next->line);
                }
            }

            return $next;

        case 'end': // Token::END
            $this->popBlock();
            $this->write('});');
            $this->closeIndentedBlocks();

            $this->inBlock = false;
            $this->blocks->pop();

            $this->transition(self::PHP);
            return $this->skip(Token::WHITESPACE, Token::DOT, Token::SEMICOLON);

        default:
            throw new Exception("Unexpected token $token->type($token->value) at line $token->line");
        }
    }

    public function stateStatement(Token $token)
    {
        switch ($token->type) {
        case Token::LCURLY:
        case Token::RCURLY:
        case Token::SEMICOLON:
            $this->appendStatement($token);
            $this->dumpStatement();
            $this->transition(self::PHP);
            return false;

        case Token::DOT:
            // check if it' just before a "should" token
            $next = $this->skip(Token::WHITESPACE, Token::EOL);
            if ($next->type === Token::SHOULD) {
                // Replace it with a single whitespace
                $token = new Token(Token::WHITESPACE, ' ');
                $this->appendStatement($token);
                return $next;
            }

            $this->appendStatement($token);
            return $next;

        case Token::SHOULD:

            // Flush captured non-statement tokens
            while (!$this->statement->isEmpty()) {
                $token = $this->statement->bottom();
                if ($token->type === Token::COMMENT ||
                    $token->type === Token::WHITESPACE ||
                    $token->type === Token::EOL) {

                    $this->write($token->value);
                    $this->statement->shift();
                } else {
                    break;
                }
            }

            // Define the expectation wrapper
            $this->write(self::SPEC_CLASS . '::expect(');
            $this->dumpStatement();
            $this->write(')->');
            $this->transition(self::SHOULD);
            return false;

        case Token::VARIABLE:
            if ($token->value === '$this') {
                switch (strtoupper($this->blocks->top())) {
                case 'DESCRIBE':
                case 'BEFORE':
                case 'AFTER':
                    $token->value = self::SPEC_CLASS . '::suite()';
                    break;
                case 'BEFORE_EACH':
                case 'AFTER_EACH':
                case 'IT':
                    $token->value = self::SPEC_CLASS . '::test()';
                    break;
                }
            }

        default:
            if ($token->token === T_CLOSE_TAG) {
                $this->dumpStatement();
                $this->transition(self::PHP);
                return $token;
            }

            $this->appendStatement($token);
            return false;
        }
    }

    public function stateShould(Token $token)
    {
        static $eol = 0;

        switch ($token->type) {
        case Token::EOL:
            // Two consecutive EOL terminate the expectation
            $token = $this->skip(Token::WHITESPACE);
            if ($token->type === Token::EOL) {
                $this->write('->do();' . str_repeat("\n", $eol+1));
                $eol = 0;
                $this->transition(self::PHP);
                return $this->skip(Token::WHITESPACE, Token::EOL, Token::SEMICOLON);
            }

            $eol++;

            return $token;
        case Token::WHITESPACE:
        case Token::DOT:
            // Ignore
            return false;

        case Token::COMMA:

            $token = $this->skip(Token::WHITESPACE, Token::EOL);
            $value = strtolower($token->value);
            if ($value === 'and' || $value === 'but') {
                $this->write('->but_'); // ,and;
                return false;
            } else if ($value === 'or') {
                $this->write('->or_');  // ,or;
                return false;
            }

            $this->write('->or_'); // default
            return $token;

        case Token::SEMICOLON:
            // Explicit termination of the expectation. Ignore it.
            $this->write('->do();' . str_repeat("\n", $eol));
            $eol = 0;
            $this->transition(self::PHP);
            return false;

        case Token::END:
            $this->Write('->do();') . str_repeat("\n", $eol);
            $eol = 0;
            $this->transition(self::PHP);
            return $token;

        // Check operators
        case $token->type === Token::TEXT &&
             array_key_exists($token->value, $this->comparisonOps):

            $this->write($this->comparisonOps[$token->value] . '_');
            return false;

        // Logical operators
        case in_array(strtolower($token->value), array('and', 'or', 'but', 'as')):

            $this->write('->' . $token->value . '_');
            return false;

        case strtolower($token->value) === 'described':
            // Ignore this token, it should always come before "as"
            return false;

        // Handle array(...) with a special case
        case strtolower($token->value) === 'array':

            $token = $this->skip(Token::WHITESPACE);
            if ($token->type === Token::LPAREN) {
                $this->write('(array');
                $token = $this->consumeParams($token);
                $this->write(')');
            } else {
                $this->write('array_');
            }
            return $token;

        case Token::IDENT:
        case $token->type === Token::TEXT &&
             preg_match('/^[A-Z_]+$/i', $token->value):

            // plain words parsed as text or idents
            $this->write($token->value . '_');
            return false;

        default:
            // Anything else should be a parameter
            $token = $this->consumeParams($token);

            // Skip dots and whitespace after it
            if ($token->type === Token::WHITESPACE || $token->type === Token::DOT) {
                $token = $this->skip(Token::WHITESPACE, Token::DOT);
            }

            return $token;
        }
    }

    protected function consumeParams(Token $token)
    {
        $parens = 0;
        $autoParens = null;

        while (true) {

            // check if we need to inject parens
            if ($autoParens === NULL) {
                $autoParens = $token->type !== Token::LPAREN;
                if ($autoParens) $this->write('(');
            }

            switch ($token->type) {

            // Control parens nesting
            case Token::LPAREN:
                $parens++;
                break;
            case Token::RPAREN:
                $parens--;
                break;

            case Token::VARIABLE:
                $this->write($token->value);
                $token = $this->skip(Token::WHITESPACE);
                if ($token->value === '->') {
                    $token = $this->skip(Token::WHITESPACE);
                    $this->write('->' . $token->value);
                    $token = $this->skip(Token::WHITESPACE);
                }
                continue 2;

            case Token::TEXT && strtolower($token->value) === 'or':
            case Token::TEXT && strtolower($token->value) === 'and':
            case Token::TEXT && strtolower($token->value) === 'but':
            case Token::TEXT && strtolower($token->value) === 'as':
            case Token::EOL:
            case Token::IDENT:
            case Token::COMMA:
            case Token::SEMICOLON:
            case Token::END:
                if ($parens === 0) {
                    if ($autoParens) $this->write(')');
                    return $token;
                }
            }

            // Write everything as parameter expression
            $this->write($token->value);

            // Skip whitespace
            $token = $this->skip(Token::WHITESPACE);
        }

        return $token;
    }

}
