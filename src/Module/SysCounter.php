<?php

/*
 * The MIT License
 *
 * Copyright (c) 2014-2024 Toha <tohenk@yahoo.com>
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

namespace NTLAB\Script\Module;

use NTLAB\Script\Core\Module;

/**
 * Counter and series functions.
 *
 * @author Toha
 * @id system.counter
 */
class SysCounter extends Module
{
    /**
     * @var array
     */
    protected $counters = [];

    /**
     * @var array
     */
    protected $series = ['alpha' => 'abcdefghijklmnopqrstuvwxyz'];

    /**
     * Get the counter value for `id`.
     *
     * @param string $id  The counter id.
     * @return int
     * @func cget
     */
    public function f_CGet($id)
    {
        return isset($this->counters[$id]) ? $this->counters[$id] : null;
    }

    /**
     * Set the counter value of `id`.
     *
     * @param string $id  The counter id
     * @param int $value  The new counter value
     * @return null
     * @func cset
     */
    public function f_CSet($id, $value)
    {
        $this->counters[$id] = $value;
        return $value;
    }

    /**
     * Set the counter value for `id` to zero.
     *
     * @param string $id  The counter id
     * @func creset
     */
    public function f_CReset($id)
    {
        return $this->f_CSet($id, 0);
    }

    /**
     * Increase counter value of `id` by one.
     *
     * @param string $id  The counter id
     * @return int
     * @func cinc
     */
    public function f_CInc($id)
    {
        if (isset($this->counters[$id])) {
            $value = $this->counters[$id];
            return $this->f_CSet($id, ++$value);
        }
    }

    /**
     * Decrease counter value of `id` by one.
     *
     * @param string $id  The counter id
     * @return int
     * @func cdec
     */
    public function f_CDec($id)
    {
        if (isset($this->counters[$id])) {
            $value = $this->counters[$id];
            return $this->f_CSet($id, --$value);
        }
    }

    /**
     * Get series from number (Alpha only).
     *
     * @param int $value  The value
     * @param string $type  Series type
     * @return string
     * @func series
     */
    public function f_Series($value, $type = 'alpha')
    {
        $result = null;
        $series = $this->series[$type];
        $div = strlen($series);
        $value = (int) $value;
        while (true) {
            if ($value == 0) {
                break;
            }
            $res = $value % $div;
            if ($res == 0) {
                $res = $div;
            }
            $value -= $res;
            $value = floor($value / $div);
            $result = $series[$res - 1].$result;
        }
        return $result;
    }
}