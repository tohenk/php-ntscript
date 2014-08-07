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

namespace NTLAB\Script\Parser;

use NTLAB\Script\Core\Script;

abstract class Parser
{
    /**
     * @var array
     */
    protected $functions = array();

    /**
     * @var array
     */
    protected $variables = array();

    /**
     * Parse a script and extract functions and/or variables.
     *
     * Script functions then can be retrieved using getFunctions() and
     * getVariables() respectively.
     *
     * @param string $expr  The script expression
     * @return \NTLAB\Script\Parser\Parser
     */
    public function parse($expr)
    {
        $this->functions = array();
        $this->variables = array();
        $this->doParse($expr);

        return $this;
    }

    /**
     * Get functions for parsed expression.
     *
     * @return array
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    /**
     * Get variables for parsed expression.
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Parse expression.
     *
     * @param string $expr  The expression
     */
    abstract protected function doParse($expr);

    /**
     * Get parser infos.
     *
     * @return array
     */
    public function getInfos()
    {
        return array();
    }
}