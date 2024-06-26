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
use NTLAB\Script\Core\Script;

/**
 * Logic functions.
 *
 * @author Toha
 * @id system.logic
 */
class SysLogic extends Module
{
    protected function evalCond($op, $expr1, $expr2)
    {
        switch (strtolower($op)) {
            case 'eq':
            case '=':
                return Script::asBool($expr1 == $expr2);
            case 'neq':
            case '<>':
                return Script::asBool($expr1 != $expr2);
            case 'leq':
            case '<=':
                return Script::asBool($expr1 <= $expr2);
            case 'geq':
            case '>=':
                return Script::asBool($expr1 >= $expr2);
            case 'ls':
            case '<':
                return Script::asBool($expr1 < $expr2);
            case 'gr':
            case '>':
                return Script::asBool($expr1 > $expr2);
        }
    }

    /**
     * Evaluate a condition and return `true` or `false` as the result.
     *
     * @param boolean $cond  The condition expression
     * @param mixed $true  Expression when true
     * @param mixed $false  Expression when false
     * @return mixed
     * @func if
     * @logic
     */
    public function f_If($cond, $true, $false = null)
    {
        return (bool) $cond ? $true : $false;
    }

    /**
     * Compare condition `expr1` and `expr2` using operator `op`.
     *
     * Available operators:
     *   expr1 =  expr2: Equal comparison
     *   expr1 <> expr2: Not equal comparison
     *   expr1 <  expr2: Less than comparison
     *   expr1 <= expr2: Less than or equal comparison
     *   expr1 >  expr2: Greater than comparison
     *   expr1 >= expr2: Greater than or equal comparison
     *
     * @param string $op  Compare condition
     * @param string $expr1  Expression 1
     * @param string $expr2  Expression 2
     * @return int
     * @func cmp
     */
    public function f_Cmp($op, $expr1, $expr2)
    {
        return $this->evalCond($op, $expr1, $expr2);
    }

    /**
     * Evaluate equal comparison ($expr1 = $expr2).
     *
     * @param string $expr1  Expression 1
     * @param string $expr2  Expression 2
     * @return int
     * @func eq
     */
    public function f_Eq($expr1, $expr2)
    {
        return $this->evalCond('=', $expr1, $expr2);
    }

    /**
     * Evaluate not equal comparison ($expr1 <> $expr2).
     *
     * @param string $expr1  Expression 1
     * @param string $expr2  Expression 2
     * @return int
     * @func neq
     */
    public function f_Neq($expr1, $expr2)
    {
        return $this->evalCond('<>', $expr1, $expr2);
    }

    /**
     * Evaluate less than or equal comparison ($expr1 <= $expr2).
     *
     * @param string $expr1  Expression 1
     * @param string $expr2  Expression 2
     * @return int
     * @func leq
     */
    public function f_Leq($expr1, $expr2)
    {
        return $this->evalCond('<=', $expr1, $expr2);
    }

    /**
     * Evaluate greater than or equal comparison ($expr1 >= $expr2).
     *
     * @param string $expr1  Expression 1
     * @param string $expr2  Expression 2
     * @return int
     * @func geq
     */
    public function f_Geq($expr1, $expr2)
    {
        return $this->evalCond('>=', $expr1, $expr2);
    }

    /**
     * Evaluate less than comparison ($expr1 < $expr2).
     *
     * @param string $expr1  Expression 1
     * @param string $expr2  Expression 2
     * @return int
     * @func ls
     */
    public function f_Ls($expr1, $expr2)
    {
        return $this->evalCond('<', $expr1, $expr2);
    }

    /**
     * Evaluate greater than comparison ($expr1 > $expr2).
     *
     * @param string $expr1  Expression 1
     * @param string $expr2  Expression 2
     * @return int
     * @func gr
     */
    public function f_Gr($expr1, $expr2)
    {
        return $this->evalCond('>', $expr1, $expr2);
    }

    /**
     * Evaluate AND conditions.
     *
     * The evaluation will be stopped when FALSE is returned, ignoring the rest.
     *
     * @param string $expr1  Expression 1
     * @param string $expr2  Expression 2
     * @param string $...  Expression 3
     * @return int
     * @func and
     */
    public function f_And()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            $state = (bool) $arg;
            if (!$state) {
                break;
            }
        }
        return Script::asBool($state);
    }

    /**
     * Evaluate OR conditions.
     *
     * The evaluation will be stopped when TRUE is returned, ignoring the rest.
     *
     * @param string $expr1  Expression 1
     * @param string $expr2  Expression 2
     * @param string $...  Expression 3
     * @return int
     * @func or
     */
    public function f_Or()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            $state = (bool) $arg;
            if ($state) {
                break;
            }
        }
        return Script::asBool($state);
    }

    /**
     * Negate the condition.
     *
     * @param string $cond  The condition
     * @return int
     * @func not
     */
    public function f_Not($cond)
    {
        return Script::asBool(!((bool) $cond));
    }

    /**
     * Evaluate if expression is null.
     *
     * @param string $expr  Expression
     * @return int
     * @func isnull
     */
    public function f_IsNull($expr)
    {
        return Script::asBool(null === $expr);
    }
}