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

namespace NTLAB\Script\Context;

class ContextIterator
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
     * Get objects.
     *
     * @return mixed
     */
    public function getObjects()
    {
        return $this->objects;
    }

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
     * @return int
     */
    public function getRecNo()
    {
        return $this->recno;
    }

    /**
     * Get record count.
     *
     * @return int
     */
    public function getRecCount()
    {
        return count($this->objects);
    }
}