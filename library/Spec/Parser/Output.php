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

namespace DrSlump\Spec\Parser;

/**
 * Generates PHP code transforming the original source of a TokenIterator
 *
 * @package     Spec\Parser
 * @author      Iván -DrSlump- Montes <drslump@pollinimini.net>
 * @see         https://github.com/drslump/Spec
 *
 * @copyright   Copyright 2011, Iván -DrSlump- Montes
 * @license     Affero GPL v3 - http://opensource.org/licenses/agpl-v3
 */
class Output
{
    /** @var TokenIterator */
    protected $_it;
    /** @var Token[] */
    protected $_stack = array();

    protected $_assertComment;

    protected $isIndent = true;
    protected $indent = 0;
    protected $indentLevels = array();

    // Flag to know if we are inside a block
    protected $blockLevel = 0;

    /**
     * Transforms the source token stream to PHP code
     *
     * @param \Iterator $it
     */
    public function generate(\Iterator $it)
    {
        $this->_it = $it;
        $it->rewind();

        do {
            // Fetch next token in the stream
            $token = $this->consume();

            // First manage indentation
            switch ($token->type) {
                case Token::DESCRIBE:
                case Token::IT:

                    $this->isIndent = false;

                    while (!empty($this->indentLevels)) {

                        $indent = array_pop($this->indentLevels);
                        if ($indent < $this->indent) {
                            array_push($this->indentLevels, $indent);
                            break;
                        }

                        $this->blockLevel--;
                        $this->stack(new Token(Token::TEXT, '});'));
                    }

                    array_push($this->indentLevels, $this->indent);

                    break;
                case Token::END:
                    $this->isIndent = false;

                    while (count($this->indentLevels)) {
                        $indent = array_pop($this->indentLevels);

                        $this->blockLevel--;
                        $end = new Token(Token::TEXT, "});");
                        $end->value = "});"; // "\n" . str_repeat(' ', $this->indent);
                        $this->stack($end);

                        if ($indent <= $this->indent) {
                            break;
                        }
                    }

                    break;
                case Token::COMMENT:
                    // Ignore comments and whitespace
                    $this->isIndent = false;
                    break;
                case Token::WHITESPACE:
                    if ($this->isIndent) {
                        $this->indent += strlen($token->value);
                        //echo "W[$this->indent]";
                    }
                    break;
                case Token::EOL:
                    // A new line flags the indent detection
                    $this->isIndent = true;
                    $this->indent = 0;
                    break;

                default:
                    $this->isIndent = false;

            }

            // Main syntax
            switch ($token->type) {

                case Token::COMMENT:
                    // Capture assertion messages
                    if (strpos($token->value, '#') === 0) {
                        $this->_assertComment = trim(substr($token->value, 1));
                    }

                    $this->stack($token);
                    echo $this->flush();
                    break;

                case Token::SEMICOLON:
                case Token::RCURLY:
                    $this->stack($token);
                    echo $this->flush();
                    break;

                case Token::DESCRIBE:

                    $this->blockLevel++;

                    echo $this->flush();
                    echo 'describe(';

                    $this->skip(array(
                        Token::WHITESPACE,
                        Token::DOT,
                    ));

                    $token = $this->consume();
                    echo $token->value;
                    echo ', function($spec){';

                    $this->skip(array(
                        Token::WHITESPACE,
                        Token::DOT,
                        Token::SEMICOLON
                    ));

                    break;

                case Token::IT:

                    $this->blockLevel++;

                    echo $this->flush();
                    echo 'it(';

                    $this->skip(array(
                        Token::WHITESPACE,
                        Token::DOT,
                    ));

                    $token = $this->consume();
                    echo $token->value;
                    echo ', function($spec){';

                    $this->skip(array(
                        Token::WHITESPACE,
                        Token::DOT,
                        Token::SEMICOLON,
                    ));
                    break;

                case Token::SHOULD:

                    $this->doShould();

                    $this->_assertComment = null;
                    break;

                case Token::END:
                    $this->_assertComment = null;

                    echo $this->flush();

                    // Already closed at the indentation handler
                    //echo '});';

                    $this->skip(array(
                        Token::WHITESPACE,
                        Token::SEMICOLON,
                    ));
                    break;

                case Token::DOT:
                    // Ignore dots. Is this OK?
                    break;

                case Token::VARIABLE:
                    if ($this->blockLevel > 0 && $token->value === '$this') {
                        $token->type = Token::TEXT;
                        $token->value = '\\DrSlump\\Spec::getContext()';
                    }

                    $this->stack($token);
                    break;


                default:
                    $this->stack($token);
                    break;
            }
        } while($it->valid());

        echo $this->flush();

        while (!empty($this->indentLevels)) {
            array_pop($this->indentLevels);
            echo "});";
        }

        echo PHP_EOL;
    }

    /**
     * @return \DrSlump\Spec\Parser\Token
     */
    protected function consume()
    {
        $item = $this->_it->current();
        $this->_it->next();
        return $item;
    }

    protected function flush($trim = false)
    {
        $out = '';
        foreach ($this->_stack as $token) {
            $out .= $token->value;
        }
        $this->_stack = array();
        return $trim ? trim($out) : $out;
    }

    protected function stack(Token $token)
    {
        if (empty($this->_stack)) {
            if (in_array($token->type, array(
                    Token::WHITESPACE,
                    Token::EOL,
                ), true)) {

                echo $token->value;
                return;
            }
        }

        $this->_stack[] = $token;
    }

    protected function skip($types = Token::WHITESPACE)
    {
        if (!$this->_it->valid()) return;

        if (!is_array($types)) $types = array($types);

        do {
            /** @var $token Token */
            $token = $this->_it->current();
            if (!in_array($token->type, $types, true)) {
                return $token;
            }
            $this->_it->next();
        } while($this->_it->valid());

        return;
    }

    protected function doShould()
    {
        // Set previous statement as the subject
        echo "expect(" . $this->flush(true);

        // Set comment argument
        if (!empty($this->_assertComment)) {
            echo ", '" . addcslashes($this->_assertComment, "'\\") . "'";
        } else {
            echo ", null";
        }

        // Always use explicit execution
        echo ", false)->";

        // Define comparison operators
        $comparisonOps = array(
            '==='   => array('same'),
            '!=='   => array('not', 'same'),
            '=='    => array('equal'),
            '!='    => array('not', 'equal'),
            '>'     => array('greater'),
            '<'     => array('less'),
            '>='    => array('at', 'least'),
            '<='    => array('at', 'most'),
        );


        // IDENT IDENT IDENT VAR , VAR , and VAR

        $eol = 0;
        $lastWasExpr = $lastWasEol = false;
        $parts = array();
        while ($this->_it->valid()) {

            // Skip spaces and dots
            $token = $this->skip(array(Token::WHITESPACE, Token::DOT));
            if (empty($token)) {
                break;
            }

            switch ($token->type) {
            case Token::EOL:
                // Two consecutive EOL terminate the expectation
                if ($lastWasEol) {
                    break 2;
                }

                // Ignore it
                $eol++;
                $lastWasEol = true;
                $token = $this->consume();
                continue 2;

            case Token::COMMA:
                $token = $this->skip();
                $value = strtolower($token->value);
                if ($value === 'and' || $value === 'but') {
                    $parts[] = '->but'; // ,and;
                    break;
                } else if ($value === 'or') {
                    $parts[] = '->or';  // ,or;
                    break;
                // @todo should have one of "foo", "bar" and "baz"
                } else {
                    $parts[] = '->or'; // default
                    // Process this token again
                    $lastWasExpr = $lastWasEol = false;
                    continue 2;
                }

                break;

            case Token::SEMICOLON:
                // Explicit termination of the expectation. Ignore it.
                $this->consume();
                break 2;

            // Check operators
            case $token->type === Token::TEXT &&
                 array_key_exists($token->value, $comparisonOps):

                $parts = array_merge($parts, $comparisonOps[$token->value]);
                break;

            // Logical operators
            case $token->type === Token::TEXT &&
                 strtolower($token->value) === 'and':
            case $token->type === Token::TEXT &&
                 strtolower($token->value) === 'or':
            case $token->type === Token::TEXT &&
                 strtolower($token->value) === 'but':
            case $token->type === Token::TEXT &&
                 strtolower($token->value) === 'as':

                if (!$lastWasExpr) {
                    $parts[] = '()';
                }
                $parts[] = '->' . strtolower($token->value);
                break;

            case $token->type === Token::TEXT &&
                 strtolower($token->value) === 'described':
                // Ignore this element completely.
                // It must always appear with "as"
                $token = $this->consume();
                continue 2;

            case $token->type === Token::TEXT &&
                 preg_match('/^[A-Z_]+$/i', $token->value):
                // plain words parsed as text are added as idents
                $parts[] = $token->value;
                break;

            default:
                if (!in_array($token->type, array(Token::IDENT, Token::FUNCTIONCALL))) {

                    // If the previous token was an expression assume we have
                    // reached the end of the expectation.
                    if ($lastWasExpr) {
                        // ignore it
                        break 2;
                    }

                    ob_start();
                    $token = $this->consumeParams();
                    $expr = ob_get_clean();

                    $lastWasExpr = true;
                    $lastWasEol = false;
                    $parts[] = $expr;
                    continue 2;
                }

                $parts[] = $token->value;
                break;
            }

            // Flag the token as not an expression
            $lastWasExpr = $lastwasEol = false;

            // Advance to next token
            $token = $this->consume();
        }

        if (!$lastWasExpr) {
            $parts[] = '()';
        }
        $parts[] = '->do();';

        //print_r($idents);
        //echo implode(' # ', $idents);

        // A bit hacky but does the job
        $mark = '~|D3L1M1T3R|~';
        $result = implode($mark, $parts);
        $result = str_replace("$mark(", '(', $result);
        $result = str_replace(")$mark", ')', $result);
        $result = str_replace("$mark", '_', $result);
        echo $result;

        // Adjust lines
        echo str_repeat("\n", $eol);
    }

    protected function consumeParams()
    {
        $parens = 0;
        $autoParens = null;

        while ($this->_it->valid()) {

            $token = $this->skip();

            // check if we need to inject parens
            if ($autoParens === NULL) {
                $autoParens = $token->type !== Token::LPAREN;
                if ($autoParens) echo '(';
            }

            switch ($token->type) {

            case Token::LPAREN:
                $parens++;
                break;
            case Token::RPAREN:
                $parens--;
                break;

            case Token::TEXT && strtolower($token->value) === 'or':
            case Token::TEXT && strtolower($token->value) === 'and':
            case Token::EOL:
            case Token::IDENT:
            case Token::COMMA:
            case Token::SEMICOLON:
                if ($parens === 0) {
                    if ($autoParens) echo ')';
                    return $token;
                }
            }

            echo $token->value;

            $token = $this->consume();
        }

        return $token;
    }

}
