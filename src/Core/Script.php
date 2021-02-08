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

use NTLAB\Script\Context\ContextIterator;
use NTLAB\Script\Context\Stack;
use NTLAB\Script\Tokenizer\Token;

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
     * @var \NTLAB\Script\Context\Stack[]
     */
    protected $stacks = [];

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
     * @param boolean $notify  Notify listener for context change
     * @return \NTLAB\Script\Core\Script
     */
    public function each($callback, $notify = true)
    {
        if (is_callable($callback)) {
            $debugs = debug_backtrace(version_compare(PHP_VERSION, '5.3.6', '>=') ? DEBUG_BACKTRACE_PROVIDE_OBJECT : true);
            // this is current function debug backtrace
            array_shift($debugs);
            // this is the current function caller debug backtrace
            $caller = array_shift($debugs);
            $i = 0;
            foreach ($this->iterator->getObjects() as $context) {
                $i++;
                $this->iterator->setRecNo($i);
                $this->setContext($context);
                if ($notify) {
                    $this->getManager()->notifyContextChange($context, $this->iterator);
                }
                call_user_func($callback, $this, isset($caller['object']) ? $caller['object'] : $this);
            }
        }
        return $this;
    }

    /**
     * Push current script context.
     *
     * @throws \RuntimeException
     * @return \NTLAB\Script\Core\Script
     */
    public function pushContext()
    {
        if (null === $this->context) {
            throw new \RuntimeException('No script context available.');
        }
        array_push($this->stacks, new Stack($this));
        return $this;
    }

    /**
     * Pop last pushed script context.
     *
     * @throws \RuntimeException
     * @return \NTLAB\Script\Core\Script
     */
    public function popContext()
    {
        if (!count($this->stacks)) {
            throw new \RuntimeException('No saved script context available.');
        }
        $stack = array_pop($this->stacks);
        $stack->restore();
        unset($stack);
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
        if ($handler = $this->getManager()->getContextHandler($context)) {
            return $handler->getMethod($context, $name);
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
        if ($retval || !$keep) {
            // replace with null if variable not found
            if (!$retval) {
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
        } else if ($all) {
            $script = str_replace($from, $to, $script);
        } else if (false !== ($p = strpos($script, $from))) {
            $script = substr($script, 0, $p).$to.substr($script, $p + strlen($from), strlen($script) - $p);
        }
    }

    /**
     * Set content from result and preserve orginal result if applicable.
     *
     * @param mixed $content
     * @param mixed $result
     * @return \NTLAB\Script\Core\Script
     */
    protected function preserveContent(&$content, $result)
    {
        // preserve return value type
        if (null === $content) {
            $content = $result;
        } else {
            if (is_array($result)) {
                error_log(sprintf('Preserving array with existing content %s!', $content));
            }
            $content .= $result;
        }
        return $this;
    }

    /**
     * Evaluate token.
     *
     * @param \NTLAB\Script\Tokenizer\Token $root  Functions token
     * @param array $vars  The parsed variables
     * @param array $caches  The variable caches
     * @param bool $keep  Keep unevaluated function
     * @return string
     */
    protected function evalToken(Token $token, $vars = [], $caches = null, $keep = false)
    {
        $content = null;
        switch ($token->getType()) {
            case Token::TOK_GROUP:
                foreach ($token->getChildren() as $ctoken) {
                    if (null !== ($result = $this->evalToken($ctoken, $vars, $caches, $keep))) {
                        $this->preserveContent($content, $result);
                    }
                }
                break;
            case Token::TOK_FUNCTION:
                $params = [];
                $logic = Manager::getInstance()->isLogic($token->getName());
                $i = 0;
                foreach ($token->getChildren() as $ctoken) {
                    $eval = true;
                    if ($logic) {
                        switch ($i) {
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
                    $params[$i] = $eval ? $this->evalToken($ctoken, $vars, $caches, $keep) : null;
                    $i++;
                }
                $replacement = $token->getContent();
                if (!$this->evalFunc($token->getName(), $params, $replacement) && !$keep) {
                    $replacement = '';
                }
                $this->preserveContent($content, $replacement);
                break;
            case Token::TOK_VARIABLE:
                $value = $token->getContent();
                $this->evalVar($token->getName(), $value, $caches, $keep);
                $this->preserveContent($content, $value);
                break;
            default:
                $this->preserveContent($content, $token->getContent());
                break;
        }
        return $content;
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
        $caches = [];
        $parser = $this->getParser();
        if ($token = $parser->parse($script)->getToken()) {
            return $this->evalToken($token, $parser->getVariables(), $caches, $keep);
        }
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