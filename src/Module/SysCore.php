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

namespace NTLAB\Script\Module;

use NTLAB\Script\Core\Module;
use NTLAB\Script\Core\Script;

/**
 * Core functions.
 *
 * @author Toha
 * @id system.core
 */
class SysCore extends Module
{
    /**
     * Create a script function.
     *
     * This function is generally used to pass as parameter for other function.
     *
     * @param string $name  The function name
     * @param string $parameter1  Function parameter
     * @param string $parameter2  Function parameter
     * @param string $...  Function parameter
     * @return string
     * @func func
     */
    public function f_Func()
    {
        return Script::asFunc(...func_get_args());
    }

    /**
     * Create a script variable.
     *
     * This function can be combined with #func() to create a complete script.
     *
     * @param string $var  Variable name
     * @return string
     * @func var
     */
    public function f_Var($var)
    {
        return Script::VARIABLE_IDENTIFIER.$var;
    }

    /**
     * Return NULL value.
     *
     * @return null
     * @func null
     */
    public function f_Null()
    {
        return null;
    }

    /**
     * Evaluate expression as PHP code.
     *
     * @param string $expr  The expression
     * @return mixed
     * @func eval
     */
    public function f_Eval($expr)
    {
        try {
            $expr = trim($expr);
            if (substr($expr, 0, 6) !== 'return') {
                $expr = 'return '.$expr;
            }
            if (substr($expr, - 1) !== ';') {
                $expr .= ';';
            }
            $result = eval($expr);
        } catch (\Exception $e) {
            error_log($e);
            $result = null;
        }

        return $result;
    }

    /**
     * Get PHP constant.
     *
     * @param string $constant  The constant name
     * @return mixed
     * @func const
     */
    public function f_Constant($constant)
    {
        return defined($constant) ? constant($constant) : null;
    }

    /**
     * Get the current script context.
     *
     * @return mixed
     * @func ctx
     */
    public function f_Context()
    {
        return $this->getScript()->getContext();
    }
}
