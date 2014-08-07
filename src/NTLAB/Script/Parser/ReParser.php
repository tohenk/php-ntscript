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

class ReParser extends Parser
{
    protected $fRe = null;
    protected $vRe = null;
    protected $pRe = null;

    /**
     * Get escaped text of regular expression character.
     *
     * @param string $ch  The character
     * @return string
     */
    protected function getEscaped($ch)
    {
        if (in_array($ch, array(
            '/', '\\', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '[', ']',
            '{', '}', '-', '+', '=', '.', '?', ',', ':', '<', '>', '\'', '"',
        ))) {
            $ch = '\\'.$ch;
        }

        return $ch;
    }

    /**
     * Get function regular expression pattern.
     *
     * @return string
     */
    public function getFRegex()
    {
        if (null == $this->fRe) {
            $this->fRe = sprintf('/%1$s([a-zA-Z\_]+[a-zA-Z0-9\_]*)%2$s((?:[^%2$s%3$s]*|(?R))*)%3$s[%4$s]*/x', $this->getEscaped(Script::FUNCTION_IDENTIFIER), $this->getEscaped(Script::FUNCTION_PARAM_START), $this->getEscaped(Script::FUNCTION_PARAM_END), $this->getEscaped(Script::STATEMENT_DELIMETER));
        }

        return $this->fRe;
    }

    /**
     * Get variable regular expression pattern.
     *
     * @return string
     */
    public function getVRegex()
    {
        if (null == $this->vRe) {
            $this->vRe = sprintf('/%1$s([a-zA-Z\_]+[a-zA-Z0-9\_%2$s]*[a-zA-Z0-9\_]*)/x', $this->getEscaped(Script::VARIABLE_IDENTIFIER), $this->getEscaped(Script::VARIABLE_SEPARATOR));
        }

        return $this->vRe;
    }

    /**
     * Get parameter regular expression pattern.
     *
     * @return string
     */
    public function getPRegex()
    {
        if (null == $this->pRe) {
            $this->pRe = sprintf('/[\s]*[%1$s]*[\s]*%2$s([^%2$s]+)%2$s[\s]*[%1$s]*[\s]*|[\s]*[%1$s]*[\s]*%3$s([^%3$s]+)%3$s[\s]*[%1$s]*[\s]*|[\s]*[%1$s]+[\s]*/x', $this->getEscaped(Script::PARAM_SEPARATOR), $this->getEscaped('\\').$this->getEscaped(Script::PARAM_QUOTE), $this->getEscaped(Script::PARAM_QUOTE_SINGLE));
        }

        return $this->pRe;
    }

    /**
     * Extra script functions.
     *
     * @param array $array  The result functions
     * @param string $script  The script expresssion
     * @return array
     */
    protected function parseFunctions(&$array, $script)
    {
        if (preg_match_all($this->getFRegex(), $script, $matches, PREG_OFFSET_CAPTURE)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $func_match = $matches[0][$i][0];
                $func_name = $matches[1][$i][0];
                $func_param = $matches[2][$i][0];
                // parse script from parameters
                $params = array();
                if ($func_param) {
                    $pmatches = $this->parseFunctions($array, $func_param);
                    // split parameters
                    $params = $this->parseParameters($func_param, $pmatches);
                }
                $array[] = array(
                    'name'   => $func_name,
                    'match'  => $func_match,
                    'params' => $params,
                );
            }

            return $matches;
        }
    }

    /**
     * Extract script variables.
     *
     * @param array $array  The result variables
     * @param string $script  The script expression
     * @return array
     */
    protected function parseVariables(&$array, $script)
    {
        if (preg_match_all($this->getVRegex(), $script, $matches, PREG_OFFSET_CAPTURE)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $var = $matches[1][$i][0];
                if (!isset($array[$var])) {
                    $array[] = $var;
                }
            }

            return $matches;
        }
    }

    /**
     * Split function parameters.
     *
     * @param string $parameter  The input parameter
     * @return array Parameters
     */
    protected function parseParameters($parameter, $funcs = array())
    {
        $array = array();
        // replace function into tag, so it is easier to split
        if (is_array($funcs) && isset($funcs[0])) {
            for ($i = 0; $i < count($funcs[0]); $i++) {
                $parameter = str_replace($funcs[0][$i][0], '%%P'.$i, $parameter);
            }
        }
        // split parameters
        $params = preg_split($this->getPRegex(), $parameter, null, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($params as $param) {
            // skip empty
            if (0 == strlen($param)) {
                continue;
            }
            // replace back
            if (preg_match_all('/%%P(\d+)/', $param, $matches, PREG_OFFSET_CAPTURE)) {
                for ($i = 0; $i < count($matches[0]); $i++) {
                    $param = str_replace($matches[0][$i][0], $funcs[0][(int) $matches[1][$i][0]][0], $param);
                }
            }
            $array[] = trim($param, '\'"');
        }

        return $array;
    }

    protected function reverseArray(&$array)
    {
        if (count($array)) {
            $tArray = $array;
            $array = array();
            while (count($tArray)) {
                $ar = array_pop($tArray);
                $array[] = $ar;
            }
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \NTLAB\Script\Parser\Parser::doParse()
     */
    public function doParse($script)
    {
        $this->parseFunctions($this->functions, $script);
        $this->parseVariables($this->variables, $script);
        $this->reverseArray($this->functions);
    }

    public function getInfos()
    {
        return array(
            'Script re' => $this->getFRegex(),
            'Variable re' => $this->getVRegex(),
            'Parameter re' => $this->getPRegex()
        );
    }
}