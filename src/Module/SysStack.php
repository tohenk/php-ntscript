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
 * Stack functions.
 *
 * @author Toha
 * @id system.stack
 */
class SysStack extends Module
{
    protected $stacks = [];

    /**
     * Clear stacks.
     *
     * @func sclr
     */
    public function f_StackClear()
    {
        $this->stacks = [];
    }

    /**
     * Check if stack with named `id` is exist.
     *
     * @param string $id  The stack id
     * @return int
     * @func sexist
     */
    public function f_StackExist($id)
    {
        return Script::asBool(isset($this->stacks[$id]));
    }

    /**
     * Push the `value` to stack named `id`.
     *
     * @param string $id  The stack id
     * @param string $value  The stack value
     * @func spush
     */
    public function f_StackPush($id, $value)
    {
        $this->stacks[$id] = $value;
    }

    /**
     * Pop the value from stack `id`.
     *
     * @param string $id  The stack id
     * @return string
     * @func spop
     */
    public function f_StackPop($id)
    {
        return isset($this->stacks[$id]) ? $this->stacks[$id] : null;
    }
}
