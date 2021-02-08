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

namespace NTLAB\Script\Core;

class Func
{
    /**
     * @var \NTLAB\Script\Core\Module
     */
    protected $module = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $description = null;

    /**
     * @var string
     */
    protected $syntax = null;

    /**
     * @var int
     */
    protected $paramCount = 0;

    /**
     * @var string
     */
    protected $method = null;

    /**
     * @var boolean
     */
    protected $logic = false;

    /**
     * @var string
     */
    protected $alias = null;

    /**
     * Constructor.
     *
     * @param \NTLAB\Script\Core\Module $module  Module owner
     * @param array $parameters  Function data
     */
    public function __construct($module, $parameters = [])
    {
        $this->module = $module;
        foreach ($parameters as $k => $v) {
            switch ($k) {
                case 'name':
                    $this->name = $v;
                    break;
                case 'description':
                    $this->description = $v;
                    break;
                case 'syntax':
                    $this->syntax = $v;
                    break;
                case 'param_cnt':
                    $this->paramCount = (int) $v;
                    break;
                case 'method':
                    $this->method = $v;
                    break;
                case 'logic':
                    $this->logic = (bool) $v;
                    break;
                case 'alias':
                    $this->alias = $v;
                    break;
            }
        }
    }

    /**
     * Get owner module.
     *
     * @return \NTLAB\Script\Core\Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Get the function name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the function description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the function syntax.
     *
     * @return string
     */
    public function getSyntax()
    {
        return $this->syntax;
    }

    /**
     * Get the function parameter count.
     *
     * @return int
     */
    public function getParameterCount()
    {
        return $this->paramCount;
    }

    /**
     * Get the function method.
     *
     * @return string|array
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Check if function is a logic.
     *
     * @return boolean
     */
    public function isLogic()
    {
        return $this->logic;
    }

    /**
     * Get the function alias.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }
}