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

namespace NTLAB\Script\Context;

class ArrayVar implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $vars = [];

    /**
     * Constructor.
     *
     * @param array $vars  The variables
     */
    public function __construct($vars = [])
    {
        foreach ($vars as $k => $v) {
            $this->add($k, $v);
        }
    }

    /**
     * Get variables.
     *
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * Add variable.
     *
     * @return \NTLAB\Script\Context\ArrayVar
     */
    public function add($name, $value)
    {
        $this->vars[$name] = $value;

        return $this;
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->vars);
    }

    public function offsetGet($offset): mixed
    {
        return $this->vars[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (null === $offset) {
            $this->vars[] = $value;
        } else {
            $this->vars[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->vars[$offset]);
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->vars);
    }

    public function count(): int
    {
        return count($this->vars);
    }

    public function __call($method, $arguments)
    {
        $var = null;
        if ('get' === substr($method, 0, 3)) {
            $method = substr($method, 3);
            // try original, ucfirst, lower, upper
            foreach ([$method, ucfirst(strtolower($method)), strtolower($method), strtoupper($method)] as $name) {
                if (array_key_exists($name, $this->vars)) {
                    $var = $name;
                    break;
                }
            }
        }
        if (null === $var) {
            throw new \BadMethodCallException(sprintf('Non existent key %s!', $method));
        }
        $value = $this->vars[$var];

        return $value;
    }
}
