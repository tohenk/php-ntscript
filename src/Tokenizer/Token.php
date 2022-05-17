<?php

/*
 * The MIT License
 *
 * Copyright (c) 2014-2021 Toha <tohenk@yahoo.com>
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

class Token implements \ArrayAccess, \Iterator, \Countable
{
    const TOK_INVALID = 0;
    const TOK_GROUP = 1;
    const TOK_TEXT = 2;
    const TOK_FUNCTION = 3;
    const TOK_VARIABLE = 4;

    /**
     * @var int
     */
    protected $type = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $content = null;

    /**
     * @var \NTLAB\Script\Tokenizer\Token[]
     */
    protected $children = [];

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * Constructor.
     *
     * @param int $type  Token type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Get token type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set token type.
     *
     * @param int $type  Token type
     * @return \NTLAB\Script\Tokenizer\Token
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get token name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set token name.
     *
     * @param string $name  Token name
     * @return \NTLAB\Script\Tokenizer\Token
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get token content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set token content.
     *
     * @param string $content  Token content
     * @return \NTLAB\Script\Tokenizer\Token
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Add child.
     *
     * @param \NTLAB\Script\Tokenizer\Token $child  The child
     * @return \NTLAB\Script\Tokenizer\Token
     */
    public function addChild(Token $child)
    {
        $this->children[] = $child;
        $this->rewind();
        return $this;
    }

    /**
     * Get child tokens.
     *
     * @return \NTLAB\Script\Tokenizer\Token[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get child matched contents.
     *
     * @return string
     */
    public function getChildrenContent()
    {
        $result = null;
        foreach ($this->children as $child) {
            $result .= $child->getContent();
        }
        return $result;
    }

    /**
     * Get child parameters.
     *
     * @return array
     */
    public function getParams()
    {
        $result = [];
        foreach ($this->children as $child) {
            $result[] = $child->getChildrenContent();
        }
        return $result;
    }

    /**
     * Collect functions.
     *
     * @param array $functions  The functions result
     */
    public function collectFunctions(&$functions)
    {
        // collect self
        switch ($this->getType()) {
            case static::TOK_FUNCTION:
                $functions[] = [
                    'name' => $this->getName(),
                    'match' => $this->getContent(),
                    'params' => $this->getParams()
                ];
                break;
        }
        // collect child
        foreach ($this->children as $child) {
            $child->collectFunctions($functions);
        }
    }

    /**
     * Collect variables.
     *
     * @param array $variables  The variables result
     */
    public function collectVars(&$variables)
    {
        // collect self
        switch ($this->getType()) {
            case static::TOK_VARIABLE:
                if (!in_array($this->getName(), $variables)) {
                    $variables[] = $this->getName();
                }
                break;
        }
        // collect child
        foreach ($this->children as $child) {
            $child->collectVars($variables);
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->children[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->children[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->children[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->children[$offset]);
    }

    public function rewind(): void
    {
        reset($this->children);
        $this->count = count($this->children);
    }

    public function key(): mixed
    {
        return key($this->children);
    }

    public function current(): mixed
    {
        return current($this->children);
    }

    public function next(): void
    {
        next($this->children);
        $this->count--;
    }

    public function valid(): bool
    {
        return $this->count > 0;
    }

    public function count(): int
    {
        return count($this->children);
    }
}