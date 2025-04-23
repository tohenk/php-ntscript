<?php

/*
 * The MIT License
 *
 * Copyright (c) 2014-2025 Toha <tohenk@yahoo.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace NTLAB\Script\Tokenizer;

use NTLAB\Script\Core\Script;
use NTLAB\Script\Stream\Stream;

class Tokenizer
{
    public const TOKENIZE_SCRIPT = 1;
    public const TOKENIZE_PARAMETER = 2;

    /**
     * @var int
     */
    protected $level = 0;

    /**
     * @var \NTLAB\Script\Stream\Stream
     */
    protected $stream = null;

    /**
     * @var \NTLAB\Script\Tokenizer\Token
     */
    protected $token = null;

    /**
     * @var string
     */
    protected $data = null;

    /**
     * @var string
     */
    protected $match = null;

    /**
     * @var int
     */
    protected $start = null;

    /**
     * @var boolean
     */
    protected $funcOk = null;

    /**
     * @var boolean
     */
    protected $paramOk = null;

    /**
     * @var int
     */
    protected $tok = null;

    /**
     * @var string
     */
    protected $quote = null;

    /**
     * Constructor.
     *
     * @param int $tok  Tokenizer type
     */
    public function __construct($tok = self::TOKENIZE_SCRIPT)
    {
        $this->tok = $tok;
    }

    /**
     * Tokenize script.
     *
     * Tokenize process should be considered as done if type is
     * Tokenize::TOKENIZE_PARAMETER and function enclosing
     * character is found.
     *
     * @param string $content  The script
     * @return \NTLAB\Script\Tokenizer\Token
     */
    public function tokenize($content)
    {
        $this->stream = Stream::create($content);
        if ($this->stream->available()) {
            $this->quote = null;
            $p = $this->stream->getPos();
            $this->log(sprintf("Tokenize '%s'", $this->stream->remain()));
            $result = new Token(Token::TOK_GROUP);
            while (true) {
                if (!($token = $this->getNext())) {
                    break;
                }
                $result->addChild($token);
                // parameter tokenize
                if ($this->tok === static::TOKENIZE_PARAMETER) {
                    if (null !== $this->quote) {
                        if ($this->stream->is($this->quote)) {
                            $this->log(sprintf("Tokenize done due to quote '%s'", $this->quote));
                            break;
                        }
                    } elseif ($this->stream->is([
                        Script::FUNCTION_PARAM_END,
                        Script::PARAM_SEPARATOR
                    ])) {
                        $this->log(sprintf("Tokenize done due to '%s'", $this->stream->getChar()));
                        break;
                    }
                }
            }
            $this->log(sprintf("Tokenize processed '%s'", $this->stream->pick($p, $this->stream->getPos() - $p)));

            return $result;
        }
    }

    /**
     * Get next token.
     *
     * @return \NTLAB\Script\Tokenizer\Token
     */
    protected function getNext()
    {
        $this->token = null;
        $this->data = null;
        $this->match = null;
        $this->funcOk = null;
        $this->paramOk = null;
        $this->start = null;
        $this->log(sprintf("Start next token at %d", $this->stream->getPos()));
        while (true) {
            // is start?
            if (null === $this->token) {
                $this->start = $this->stream->getPos();
                if (Token::TOK_INVALID === ($tokenType = $this->getType())) {
                    break;
                }
                $this->token = new Token($tokenType);
            }
            // process next char
            if (!$this->readData()) {
                $this->log(sprintf("Next token done at %d", $this->stream->getPos()));
                break;
            }
        }
        if ($this->token) {
            $this->token->setName($this->data);
            $this->token->setContent($this->match);
        }

        return $this->token;
    }

    /**
     * Get token type for current character.
     *
     * @return int
     */
    protected function getType()
    {
        $token = Token::TOK_INVALID;
        if ($this->stream->read()) {
            $this->log(sprintf("Checking token type '%s'", $this->stream->getChar()));
            switch (true) {
                case $this->stream->is(Script::VARIABLE_IDENTIFIER):
                    $token = Token::TOK_VARIABLE;
                    break;
                case $this->stream->is(Script::FUNCTION_IDENTIFIER):
                    $token = Token::TOK_FUNCTION;
                    break;
                case static::TOKENIZE_PARAMETER === $this->tok && $this->stream->is([Script::PARAM_QUOTE, Script::PARAM_QUOTE_SINGLE]):
                    // is this first occurance of quote?
                    if (null === $this->quote) {
                        $this->quote = $this->stream->getChar();
                        $this->log(sprintf("Got quote '%s'", $this->quote));
                        // advance start if next type is function
                        switch ($token = $this->getType()) {
                            case Token::TOK_FUNCTION:
                                $this->start++;
                                break;
                        }
                    } else {
                        // no, just treat as text
                        $token = Token::TOK_TEXT;
                        $this->data = $this->stream->getChar();
                    }
                    break;
                default:
                    $token = Token::TOK_TEXT;
                    $this->data = $this->stream->getChar();
                    break;
            }
        }

        return $token;
    }

    /**
     * Read token data.
     *
     * @return boolean
     */
    protected function readData()
    {
        $next = $this->stream->read();
        switch ($this->token->getType()) {
            case Token::TOK_VARIABLE:
                if ($next) {
                    // variable need valid name
                    if (!$this->isValid(Script::VARIABLE_SEPARATOR)) {
                        $next = false;
                        if (!$this->checkDone()) {
                            $this->stream->prev();
                        }
                    }
                }
                if (!$next) {
                    $this->match = Script::VARIABLE_IDENTIFIER.$this->data;
                    $this->log(sprintf("Got variable '%s'", $this->match));
                }
                break;
            case Token::TOK_FUNCTION:
                // need valid identifier for function name
                if ($next) {
                    // check function name
                    if (null === $this->funcOk) {
                        if (!$this->isValid()) {
                            // must be parameter opening
                            if ($this->stream->is(Script::FUNCTION_PARAM_START)) {
                                $this->log(sprintf("Got function name '%s'", $this->data));
                                $this->funcOk = true;
                            } else {
                                $next = false;
                            }
                        }
                    }
                    // check function parameter
                    if ($this->funcOk) {
                        if ($next = $this->readParameter()) {
                            $this->log(sprintf("Got function parameter '%s'", $this->data));
                            $this->paramOk = true;
                        }
                    }
                    // check closing parenthesis
                    if ($this->paramOk) {
                        // stop tokenize for function
                        $next = false;
                        // must have closing parenthesis
                        if ($this->stream->read() && $this->stream->is(Script::FUNCTION_PARAM_END)) {
                            $this->log(sprintf("Got function end '%s'", $this->data));
                            $this->stream->skip(Script::STATEMENT_DELIMETER);
                            $this->match = $this->stream->pick($this->start, $this->stream->getPos() - $this->start);
                            $this->log(sprintf("Got function '%s'", $this->match));
                            if (null !== $this->quote) {
                                $this->stream->skip($this->quote);
                                $this->log(sprintf("Skipping quote %s", $this->quote));
                            }
                            break;
                        }
                    }
                }
                if (!$next) {
                    // function invalid, assume as text
                    $this->token->setType(Token::TOK_TEXT);
                    $this->match = Script::FUNCTION_IDENTIFIER.$this->data;
                    $this->data = null;
                }
                break;
            default:
                if ($next) {
                    if ($this->checkDone()) {
                        $next = false;
                    } else {
                        // check if function or variable identifier found
                        if ($this->stream->is([Script::FUNCTION_IDENTIFIER, Script::VARIABLE_IDENTIFIER])) {
                            $next = false;
                            $this->stream->prev();
                        } else {
                            $this->data .= $this->stream->getChar();
                        }
                    }
                }
                if (!$next) {
                    $this->match = $this->data;
                    $this->data = null;
                    $this->log(sprintf("Got text '%s'", $this->match));
                }
                break;
        }

        return $next;
    }

    /**
     * Check if tokenize process should stop.
     *
     * @return boolean
     */
    protected function checkDone()
    {
        if ($this->tok === static::TOKENIZE_PARAMETER) {
            // check for stop chars e.g quote char ' or "
            if (null !== $this->quote) {
                if ($this->stream->is($this->quote)) {
                    $this->log(sprintf("Got quote '%s' at %d", $this->quote, $this->stream->getPos()));

                    return true;
                }
            } elseif ($this->stream->is([Script::FUNCTION_PARAM_END, Script::PARAM_SEPARATOR])) {
                $this->log(sprintf("Got parameter end '%s' at %d", $this->stream->getChar(), $this->stream->getPos()));
                $this->stream->prev();

                return true;
            }
        }

        return false;
    }

    /**
     * Read function parameters.
     *
     * @return boolean
     */
    protected function readParameter()
    {
        $done = false;
        $this->log(sprintf("Read parameter start for '%s'", $this->data));
        while (true) {
            $this->log(sprintf("Current pos %d", $this->stream->getPos()));
            // clean white spaces
            if ($valid = $this->stream->skipWhitespace()) {
                $this->log(sprintf("Cleaned pos %d = '%s'", $this->stream->getPos(), $this->stream->getChar()));
                // no paramater found
                if ($this->stream->is(Script::FUNCTION_PARAM_END)) {
                    break;
                }
                $this->log(sprintf("Tokenize at %d with '%s'", $this->stream->getPos(), $this->stream->remain()));
                $tokenizer = new self(static::TOKENIZE_PARAMETER);
                $tokenizer->level = $this->level + 1;
                if (($tokens = $tokenizer->tokenize($this->stream)) && count($tokens)) {
                    $this->log(sprintf("Tokenize end at %d, remain is '%s'", $this->stream->getPos(), $this->stream->remain()));
                    $this->log(sprintf("Current char '%s'", $this->stream->getChar()));
                    $valid = $this->stream->skipWhitespace();
                    $this->log(sprintf("Current char no whitespace '%s'", $this->stream->getChar()));
                    // no more parameters
                    if ($valid && $this->stream->is(Script::FUNCTION_PARAM_END)) {
                        $done = true;
                        $this->log(sprintf("Done '%s' at %d", $this->data, $this->stream->getPos()));
                    }
                    // next must be parameter separator
                    if (!$done && $valid && !$this->stream->skip(Script::PARAM_SEPARATOR)) {
                        $valid = false;
                    }
                    if ($valid) {
                        $this->token->addChild($tokens);
                    }
                } else {
                    $valid = false;
                }
                unset($tokenizer);
                if (!$valid || $done) {
                    break;
                }
            }
        }
        $this->log(sprintf("Read parameter end for '%s'", $this->data));

        return $valid;
    }

    /**
     * Check if current char is valid identifier.
     *
     * @param string $allow  Allowed characters
     * @return boolean
     */
    protected function isValid($allow = null)
    {
        // digits are not allowed in indentifier as prefix
        if (0 === strlen((string) $this->data) && $this->stream->isDigit()) {
            return false;
        }
        if (false === strpos((string) $allow, $this->stream->getChar()) && !preg_match('/[a-zA-Z0-9\_]/', $this->stream->getChar())) {
            return false;
        }
        $this->data .= $this->stream->getChar();

        return true;
    }

    /**
     * Log a message.
     *
     * @param string $str  The message
     */
    protected function log($str)
    {
        //echo sprintf("%2d> %s\n", $this->level, str_repeat(' ', $this->level * 2).$str);
    }
}
