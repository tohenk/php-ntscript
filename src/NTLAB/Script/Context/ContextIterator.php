<?php

/*
 * The MIT License
 *
 * Copyright (c) 2014 Toha <tohenk@yahoo.com>
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

class ContextIterator implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $objects;

    /**
     * @var int
     */
    protected $recno;

    /**
     * Set objects.
     *
     * @param mixed $objects  Objects context
     * @return \NTLAB\Script\Context\ContextIterator
     */
    public function setObjects($objects)
    {
        $this->objects = $objects;

        return $this;
    }

    /**
     * Set record number.
     *
     * @param int $recno  Record number
     * @return \NTLAB\Script\Context\ContextIterator
     */
    public function setRecNo($recno)
    {
        $this->recno = $recno;

        return $this;
    }

    /**
     * Get record number.
     *
     * @return number
     */
    public function getRecNo()
    {
        return $this->recno;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->objects);
    }

    public function offsetGet($offset)
    {
        return $this->objects[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->objects[] = $value;
        } else {
            $this->objects[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->objects[$offset]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->objects);
    }

    public function count()
    {
        return count($this->objects);
    }
}