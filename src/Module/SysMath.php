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

namespace NTLAB\Script\Module;

use NTLAB\Script\Core\Module;

/**
 * Math functions.
 *
 * @author Toha
 * @id system.math
 */
class SysMath extends Module
{
    /**
     * Sum `value1` with `value2` and so on.
     *
     * @param float $value1  Value 1
     * @param float $value2  Value 2
     * @param float $...  Value 3
     * @return float
     * @func sum
     */
    public function f_Sum()
    {
        $value = null;
        $args = func_get_args();
        for ($i = 0; $i < count($args); $i ++) {
            if (is_numeric($args[$i])) {
                if ($i == 0) {
                    $value = $args[$i];
                } else {
                    $value += $args[$i];
                }
            }
        }
        return $value;
    }

    /**
     * Subtract `value1` with `value2` and so on.
     *
     * @param float $value1  Value 1
     * @param float $value2  Value 2
     * @param float $...  Value 3
     * @return float
     * @func sub
     */
    public function f_Sub()
    {
        $value = null;
        $args = func_get_args();
        for ($i = 0; $i < count($args); $i ++) {
            if (is_numeric($args[$i])) {
                if ($i == 0) {
                    $value = $args[$i];
                } else {
                    $value -= $args[$i];
                }
            }
        }
        return $value;
    }

    /**
     * Multiply `value1` with `value2` and so on.
     *
     * @param float $value1  Value 1
     * @param float $value2  Value 2
     * @param float $...  Value 3
     * @return float
     * @func mul
     */
    public function f_Mul()
    {
        $value = null;
        $args = func_get_args();
        for ($i = 0; $i < count($args); $i ++) {
            if (is_numeric($args[$i])) {
                if ($i == 0) {
                    $value = $args[$i];
                } else {
                    $value *= $args[$i];
                }
            }
        }
        return $value;
    }

    /**
     * Divide `value1` with `value2` and so on.
     *
     * @param float $value1  Value 1
     * @param float $value2  Value 2
     * @param float $...  Value 3
     * @return float
     * @func div
     */
    public function f_Div()
    {
        $value = null;
        $args = func_get_args();
        for ($i = 0; $i < count($args); $i ++) {
            if (is_numeric($args[$i])) {
                if ($i == 0) {
                    $value = $args[$i];
                } else {
                    $value /= $args[$i];
                }
            }
        }
        return $value;
    }

    /**
     * Modulo (remainder) of `value1` divided by `value2`.
     *
     * @param int $value1  Value 1
     * @param int $value2  Value 2
     * @return int
     * @func mod
     */
    public function f_Mod($value1, $value2)
    {
        if (is_numeric($value1) && is_numeric($value2)) {
            return $value1 % $value2;
        }
    }

    /**
     * Increase `value` by 1.
     *
     * @param int $value  The value
     * @return int
     * @func inc
     */
    public function f_Inc($value)
    {
        if (is_numeric($value)) {
            return ++$value;
        }
    }

    /**
     * Decrease `value` by 1.
     *
     * @param int $value  The value
     * @return int
     * @func dec
     */
    public function f_Dec($value)
    {
        if (is_numeric($value)) {
            return --$value;
        }
    }

    /**
     * Cast number as integer.
     *
     * @param float $v  The value
     * @return int
     * @func int
     */
    public function f_Int($v)
    {
        return (int) $v;
    }

    /**
     * Filter numbers only from value (remove non number).
     *
     * @param string $value  The value to filter
     * @return string
     * @func numonly
     */
    public function f_NumbersOnly($value)
    {
        return preg_replace('/[^0-9]/', '', $value);
    }
}