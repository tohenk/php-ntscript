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

namespace NTLAB\Script\Core;

use NTLAB\Script\Context\ContextIterator;

class Script
{
    const FUNCTION_IDENTIFIER = '#';
    const FUNCTION_PARAM_START = '(';
    const FUNCTION_PARAM_END = ')';
    const VARIABLE_IDENTIFIER = '$';
    const VARIABLE_SEPARATOR = '.';
    const PARAM_SEPARATOR = ',';
    const PARAM_QUOTE = '"';
    const PARAM_QUOTE_SINGLE = '\'';
    const STATEMENT_DELIMETER = ';';

    /**
     * @var \NTLAB\Script\Core\Manager
     */
    protected $manager = null;

    /**
     * @var array
     */
    protected $contexes = array();

    /**
     * @var \NTLAB\Script\Context\ContextIterator
     */
    protected $iterator = null;

    /**
     * @var object
     */
    protected $context = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->manager = Manager::getInstance();
        $this->iterator = new ContextIterator();
    }

    /**
     * Get script manager.
     *
     * @return \NTLAB\Script\Core\Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Get script parser.
     *
     * @return \NTLAB\Script\Parser\Parser
     */
    public function getParser()
    {
        return $this->getManager()->getParser();
    }

    /**
     * Get context iterator.
     *
     * @return \NTLAB\Script\Context\ContextIterator
     */
    public function getIterator()
    {
        return $this->iterator;
    }

    /**
     * Set iterator objects.
     *
     * @param mixed $objects  Objects context
     * @return \NTLAB\Script\Core\Script
     */
    public function setObjects($objects)
    {
        $this->iterator->setObjects($objects);

        return $this;
    }

    /**
     * Process each context.
     *
     * @param mixed $callback  The callback
     * @return \NTLAB\Script\Core\Script
     */
    public function each($callback)
    {
        if (is_callable($callback)) {
            $debugs = debug_backtrace(version_compare(PHP_VERSION, '5.3.6', '>=') ? DEBUG_BACKTRACE_PROVIDE_OBJECT : true);
            // this is current function debug backtrace
            $current = array_shift($debugs);
            // this is the current function caller debug backtrace
            $caller = array_shift($debugs);
            $i = 0;
            foreach ($this->iterator as $context) {
                $i++;
                $this->iterator->setRecNo($i);
                $this->setContext($context);
                $this->getManager()->notifyContextChange($context, $this->iterator);
                call_user_func($callback, $this, isset($caller['object']) ? $caller['object'] : $this);
            }
        }

        return $this;
    }

    /**
     * Push current script context.
     *
     * @throws RuntimeException
     * @return \NTLAB\Script\Core\Script
     */
    public function pushContext()
    {
        if (null === $this->context) {
            throw new \RuntimeException('No script context available.');
        }
        array_push($this->contexes, $this->context);

        return $this;
    }

    /**
     * Pop last pushed script context.
     *
     * @throws RuntimeException
     * @return \NTLAB\Script\Core\Script
     */
    public function popContext()
    {
        if (! count($this->contexes)) {
            throw new \RuntimeException('No saved script context available.');
        }
        $this->context = array_pop($this->contexes);

        return $this;
    }

    /**
     * Get script context.
     *
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set script context variables.
     *
     * @param mixed $context  The variable context
     * @return \NTLAB\Script\Core\Script
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Evaluate script function.
     *
     * @param string $func  The function name
     * @param array $parameters  The function parameters
     * @param string $result  Evaluated script
     * @return bool True if function evaluated sucessfully
     */
    public function evalFunc($func, $parameters, &$result)
    {
        if ($this->manager->has($func)) {
            $result = $this->manager->call($this, $func, $parameters);

            return true;
        }

        return false;
    }

    /**
     * Get variable method.
     *
     * @param mixed $context  The object context
     * @param string $name  The variable name
     * @return string
     */
    public function getVarMethod($context, $name)
    {
        foreach ($this->getManager()->getContexes() as $handler) {
            if ($handler->canHandle($context)) {
                return $handler->getMethod($context, $name);
            }
        }
    }

    /**
     * Get variable value.
     *
     * @param mixed $context  The object context
     * @param string $name  The variable name
     * @return mixed
     */
    public function getVarValue($context, $name)
    {
        if ($method = $this->getVarMethod($context, $name)) {
            try {
                if (is_callable($method)) {
                    return call_user_func($method);
                } else {
                    return $context->$method();
                }
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Get variable context.
     *
     * @param mixed $context  The context
     * @param string $var  The variable name
     */
    public function getVarContext(&$context, &$var)
    {
        if ($context) {
            if (false !== strpos($var, static::VARIABLE_SEPARATOR)) {
                list($method, $var) = explode(static::VARIABLE_SEPARATOR, $var, 2);
                if (is_object($object = $this->getVarValue($context, $method))) {
                    $context = $object;
                    $this->getVarContext($context, $var);
                } else {
                    $context = null;
                }
            }
        }
    }

    /**
     * Get variable value.
     *
     * @param mixed $var  The variable return value
     * @param string $name  The variable name
     * @param object $context  The variable context
     * @return bool
     */
    public function getVar(&$var, $name, $context)
    {
        $this->getVarContext($context, $name);
        if ($context) {
            if ($method = $this->getVarMethod($context, $name)) {
                try {
                    if (is_callable($method)) {
                        $var = call_user_func($method);
                    } else {
                        $var = $context->$method();
                    }

                    return true;
                } catch (\Exception $e) {
                }
            }
        }

        return false;
    }

    /**
     * Evaluate script variable from the context object.
     *
     * @param string $var  The variable name
     * @param string $result  Evaluated variable
     * @param array $caches  The variable caches
     * @param bool $keep  True to keep the variable intact if not evaluated
     * @return True if variable evaluated successfully
     */
    public function evalVar($var, &$result, $caches = null, $keep = false)
    {
        $retval = false;
        if (null !== $caches && array_key_exists($var, $caches)) {
            $retval = true;
            $value = $caches[$var];
        } elseif ($this->getVar($value, $var, $this->context)) {
            $retval = true;
        }
        if ($retval || ! $keep) {
            // replace with null if variable not found
            if (! $retval) {
                $value = null;
            }
            if ($result == static::VARIABLE_IDENTIFIER.$var) {
                $result = $value;
            } else {
                $result = str_replace(static::VARIABLE_IDENTIFIER.$var, $value, $result);
            }
        }
        
        return $retval;
    }

    /**
     * Replace script part.
     *
     * @param string $script  The original script
     * @param string $from  The part to be replaced
     * @param string $to  The replacement
     * @param boolean $all  Replace all
     */
    protected function replaceScript(&$script, $from, $to, $all = false)
    {
        if ($script == $from) {
            $script = $to;
        } else {
            if ($all) {
                $script = str_replace($from, $to, $script);
            } else 
                if (false !== ($p = strpos($script, $from))) {
                    $script = substr($script, 0, $p).$to.substr($script, $p + strlen($from), strlen($script) - $p);
                }
        }
    }

    /**
     * Evaluate expression.
     *
     * @param string $script  The expression
     * @param array $funcs  The parsed functions
     * @param array $vars  The parsed variables
     * @param array $caches  The variable caches
     * @param bool $keep  Keep unevaluated function
     * @return string
     */
    protected function evalExpr($script, $funcs = array(), $vars = array(), $caches = null, $keep = false)
    {
        // eval functions
        for ($i = 0; $i < count($funcs); $i++) {
            $keys = array_keys($funcs);
            $current = $keys[$i];
            $fdata = $funcs[$current];
            $fname = $fdata['match'];
            // check if script is string and function is still exist in source
            if (! is_string($script) || 0 == strlen($script) || false === strpos($script, $fname)) {
                continue;
            }
            // evaluate parameters
            $params = array();
            $logic = Manager::getInstance()->isLogic($fdata['name']);
            for ($j = 0; $j < count($fdata['params']); $j++) {
                $eval = true;
                if ($logic) {
                    switch ($j) {
                        case 1:
                            // expect TRUE
                            // do not process if condition is FALSE
                            if (false === (bool) $params[0]) {
                                $eval = false;
                            }
                            break;
                        
                        case 2:
                            // expect FALSE
                            // do not process if condition is TRUE
                            if (true === (bool) $params[0]) {
                                $eval = false;
                            }
                            break;
                    }
                }
                $params[$j] = $eval ? $this->evalExpr($fdata['params'][$j], $funcs, $vars, $caches, $keep) : null;
            }
            // evaluate functions
            $replacement = $fname;
            if (! $this->evalFunc($fdata['name'], $params, $replacement) && ! $keep) {
                $replacement = '';
            }
            // replace the result
            $this->replaceScript($script, $fname, $replacement);
            // replace matched functions
            for ($j = $i + 1; $j < count($funcs); $j++) {
                // replace if only match parameter
                $repl = false;
                for ($k = 0; $k < count($funcs[$keys[$j]]['params']); $k++) {
                    $p = $funcs[$keys[$j]]['params'][$k];
                    $this->replaceScript($p, $fname, $replacement, true);
                    if ($p != $funcs[$keys[$j]]['params'][$k]) {
                        $funcs[$keys[$j]]['params'][$k] = $p;
                        $repl = true;
                    }
                }
                if ($repl) {
                    $p = $funcs[$keys[$j]]['match'];
                    $this->replaceScript($p, $fname, $replacement, true);
                    if ($p != $funcs[$keys[$j]]['match']) {
                        $funcs[$keys[$j]]['match'] = $p;
                    }
                }
            }
        }
        // eval variables
        foreach ($vars as $var) {
            // check if variable still exist
            if (false === strpos($script, static::VARIABLE_IDENTIFIER.$var)) {
                continue;
            }
            $this->evalVar($var, $script, $caches, $keep);
        }

        return $script;
    }

    /**
     * Evaluate a script expression.
     *
     * @param string $script  The expression
     * @param bool $keep  Wheter to keep unknows function or not
     * @return string The parsed script
     */
    public function evaluate($script, $keep = false)
    {
        $caches = array();
        $this->getParser()->parse($script);

        return $this->evalExpr($script,
            $this->getParser()->getFunctions(),
            $this->getParser()->getVariables(),
            $caches, $keep);
    }

    /**
     * Parse script boolean value.
     *
     * @param bool $value  The value
     * @return int 1 if true otherwise 0
     */
    public static function asBool($value)
    {
        return $value ? 1 : 0;
    }
}