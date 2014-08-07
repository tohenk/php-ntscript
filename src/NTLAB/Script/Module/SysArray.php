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

namespace NTLAB\Script\Module;

use NTLAB\Script\Core\Module;
use NTLAB\Script\Core\Script;

/**
 * Array and list functions.
 *
 * @author Toha
 * @id system.array
 */
class SysArray extends Module
{
    /**
     * @var \NTLAB\Script\Core\Script
     */
    protected static $script = null;

    protected static $eachObject = null;

    /**
     * @var array
     */
    protected static $list = array();

    /**
     * Get script object.
     *
     * @return \NTLAB\Script\Core\Script
     */
    protected function getScript()
    {
        if (null == self::$script) {
            self::$script = new Script();
        }

        return self::$script;
    }

    /**
     * Process objects member and evaluate the script.
     *
     * @param array $objects  The objects
     * @param string $script  The script expression to evaluate
     * @param boolean $decode  Wheter script is decoded or not (using base64)
     * @func each
     */
    public function f_ForEach($objects, $script, $decode = false)
    {
        if (is_array($objects) || is_a($objects, 'ArrayObject')) {
            if ($decode) {
                $script = base64_decode($script);
            }
            foreach ($objects as $object) {
                self::$eachObject = $object;
                $this->getScript()->evaluate($script);
            }
        }
    }

    /**
     * Get the value from object currently processed by `#each()`.
     *
     * @param string $var  The column name
     * @return mixed
     * @func eachvar
     */
    public function f_ForEachVar($var)
    {
        $value = null;
        $this->getScript()->getVar($value, $var, self::$eachObject);

        return $value;
    }

    /**
     * Create a list named `name`.
     *
     * @param string $name  The list name
     * @func lcreate
     */
    public function f_LCreate($name)
    {
        self::$list[$name] = array();
    }

    /**
     * Add value to list named `name`.
     *
     * @param string $name  The list name
     * @param mixed $value  The list value
     * @func ladd
     */
    public function f_LAdd($name, $value)
    {
        if (!isset(self::$list[$name])) {
            $this->f_LCreate($name);
        }
        self::$list[$name][] = $value;
    }

    /**
     * Concatenate list values.
     *
     * @param string $name  The list name
     * @param string $delim  The values delimeter
     * @return string
     * @func lconcat
     */
    public function f_LConcat($name, $delim = ' ')
    {
        if (isset(self::$list[$name])) {
            return implode($delim, self::$list[$name]);
        }
    }
}