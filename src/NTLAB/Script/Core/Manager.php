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

use NTLAB\Script\Context\ContextInterface;
use NTLAB\Script\Context\ContextIterator;
use NTLAB\Script\Context\ArrayContext;
use NTLAB\Script\Context\ObjectContext;
use NTLAB\Script\Listener\ListenerInterface;
use NTLAB\Script\Parser\Parser;
use NTLAB\Script\Parser\LexerParser;
use NTLAB\Script\Provider\ProviderInterface;
use NTLAB\Script\Provider\SystemProvider;

// register system module provider
Manager::addProvider(SystemProvider::getInstance());

// register core context handler
Manager::addContext(ObjectContext::getInstance(), Manager::CONTEXT_PRIO_LOW);
Manager::addContext(ArrayContext::getInstance(), Manager::CONTEXT_PRIO_LOW);

class Manager
{
    const VERSION = '1.0.0';

    const CONTEXT_PRIO_HIGH = 'HIGH';
    const CONTEXT_PRIO_NORMAL = 'NORMAL';
    const CONTEXT_PRIO_LOW = 'LOW';

    /**
     * @var \NTLAB\Script\Core\Manager
     */
    protected static $instance = null;

    /**
     * @var \NTLAB\Script\Provider\ProviderInterface[]
     */
    protected static $providers = array();

    /**
     * @var \NTLAB\Script\Listener\ListenerInterface[]
     */
    protected static $listeners = array();

    /**
     * @var \NTLAB\Script\Context\ContextInterface[]
     */
    protected static $contexes = array(
        self::CONTEXT_PRIO_HIGH => array(),
        self::CONTEXT_PRIO_NORMAL => array(),
        self::CONTEXT_PRIO_LOW => array(),
    );

    /**
     * @var \NTLAB\Script\Parser\Parser
     */
    protected static $parser;

    /**
     * @var \NTLAB\Script\Core\Module[]
     */
    protected $modules = array();

    /**
     * @var \NTLAB\Script\Core\Func[]
     */
    protected $functions = array();

    /**
     * @var array
     */
    protected $logics = array();

    /**
     * Get script manager instance.
     *
     * @return \NTLAB\Script\Core\Manager
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new self();
            self::$instance
                ->registerProviders()
                ->notifyModuleRegister()
            ;
        }

        return self::$instance;
    }

    /**
     * Add script module provider.
     *
     * @param \NTLAB\Script\Provider\ProviderInterface $provider  Module provider
     */
    public static function addProvider(ProviderInterface $provider)
    {
        if (!in_array($provider, self::$providers)) {
            self::$providers[] = $provider;
        }
    }

    /**
     * Add module registration listener.
     *
     * @param \NTLAB\Script\Listener\ListenerInterface $listener  The listener
     */
    public static function addListener(ListenerInterface $listener)
    {
        if (!in_array($listener, self::$listeners)) {
            self::$listeners[] = $listener;
        }
    }

    /**
     * Add script context handler.
     *
     * @param \NTLAB\Script\Context\ContextInterface $context  Context handler
     */
    public static function addContext(ContextInterface $context, $priority = self::CONTEXT_PRIO_NORMAL)
    {
        if (!in_array($context, self::$contexes[$priority])) {
            self::$contexes[$priority][] = $context;
        }
    }

    /**
     * Register default script parser.
     *
     * @param \NTLAB\Script\Parser\Parser $parser  The default parser
     */
    public static function registerParser(Parser $parser)
    {
        self::$parser = $parser;
    }

    /**
     * Get context handlers.
     *
     * @return \NTLAB\Script\Context\ContextInterface[]
     */
    public static function getContexes()
    {
        return array_merge(
            static::$contexes[static::CONTEXT_PRIO_HIGH],
            static::$contexes[static::CONTEXT_PRIO_NORMAL],
            static::$contexes[static::CONTEXT_PRIO_LOW]
        );
    }

    /**
     * Get context handler.
     *
     * @param mixed $context  The context
     * @return \NTLAB\Script\Context\ContextInterface
     */
    public static function getContextHandler($context)
    {
        foreach (static::getContexes() as $handler) {
            if ($handler->canHandle($context)) {
                return $handler;
            }
        }
    }

    /**
     * Register all module provided by the providers.
     *
     * @return \NTLAB\Script\Core\Manager
     */
    protected function registerProviders()
    {
        foreach (self::$providers as $provider) {
            if (!is_array($modules = $provider->getModules())) {
                continue;
            }
            foreach ($modules as $module) {
                $this->addModule($module);
            }
        }

        return $this;
    }

    /**
     * Notify all listener for module registration.
     *
     * @return \NTLAB\Script\Core\Manager
     */
    protected function notifyModuleRegister()
    {
        foreach (self::$listeners as $listener) {
            $listener->notifyModuleRegister($this);
        }

        return $this;
    }

    /**
     * Notify all listener for context change.
     *
     * @param mixed $context  Script context
     * @param \NTLAB\Script\Context\ContextIterator $iterator  Context iterator
     * @return \NTLAB\Script\Core\Manager
     */
    public function notifyContextChange($context, ContextIterator $iterator)
    {
        foreach (self::$listeners as $listener) {
            $listener->notifyContextChange($context, $iterator);
        }

        return $this;
    }

    /**
     * Get default script parser.
     *
     * @return \NTLAB\Script\Parser\Parser
     */
    public function getParser()
    {
        if (null === self::$parser) {
            $this->registerParser(new LexerParser());
        }

        return self::$parser;
    }

    /**
     * Add script module.
     *
     * @param \NTLAB\Script\Module\Module $module  The module to add
     * @return \NTLAB\Script\Core\Manager
     */
    public function addModule(Module $module)
    {
        if (($id = $module->getId()) && ! isset($this->modules[$id])) {
            $this->modules[$id] = $module;
            $module->setManager($this)->register();
        }

        return $this;
    }

    /**
     * Get script module instance.
     *
     * @param string $id  The module id
     * @return \NTLAB\Script\Module\Module
     */
    public function getModule($id)
    {
        return isset($this->modules[$id]) ? $this->modules[$id] : null;
    }

    /**
     * Add script function.
     *
     * @param \NTLAB\Script\Core\Func $func  The function
     * @return \NTLAB\Script\Core\Manager
     */
    public function add(Func $func)
    {
        foreach (array($func->getName(), $func->getAlias()) as $name) {
            if (null === $name) {
                continue;
            }
            $this->registerFunc($name, $func);
            if ($func->isLogic()) {
                $this->registerLogic($name);
            }
        }

        return $this;
    }

    /**
     * Register script function.
     *
     * @param string $name  The function name
     * @param \NTLAB\Script\Core\Function $func  The function object
     * @return \NTLAB\Script\Core\Manager
     */
    protected function registerFunc($name, $func)
    {
        if (!isset($this->functions[$name])) {
            $this->functions[$name] = $func;
        }

        return $this;
    }

    /**
     * Register script logic.
     *
     * @param string $name  The function name
     * @return \NTLAB\Script\Core\Manager
     */
    protected function registerLogic($name)
    {
        if (!in_array($name, $this->logics)) {
            $this->logics[] = $name;
        }

        return $this;
    }

    /**
     * Add function alias.
     *
     * @param string $alias  Function name alias
     * @param string $func  The original function name to alias
     * @return \NTLAB\Script\Core\Manager
     */
    public function addAlias($alias, $func)
    {
        if (!$this->has($alias) && ($func = $this->getFunc($func))) {
            $this->registerFunc($alias, $func);
            if ($func->isLogic()) {
                $this->registerLogic($alias);
            }
        }

        return $this;
    }

    /**
     * Check if function exist.
     *
     * @param string $name  The function name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->functions[$name]) ? true : false;
    }

    /**
     * Check if function is a logic.
     *
     * @param string $name  The function name
     * @return boolean
     */
    public function isLogic($name)
    {
        return in_array($name, $this->logics) ? true : false;
    }

    /**
     * Get function descriptor.
     *
     * @param string $name  Function name
     * @return \NTLAB\Script\Core\Func
     */
    protected function getFunc($name)
    {
        if (isset($this->functions[$name])) {
            return $this->functions[$name];
        }
    }

    /**
     * Call function.
     *
     * @param \NTLAB\Script\Core\Script $caller  Caller script
     * @param string $name  The function name
     * @param array $parameters  The parameters
     * @return string
     */
    public function call(Script $caller, $name, $parameters = array())
    {
        if ($this->has($name) && ($func = $this->getFunc($name))) {
            if (count($parameters) >= $func->getParameterCount()) {
                $method = $func->getMethod();
                if (is_callable($method)) {
                    $func->getModule()->setScript($caller);
                    return call_user_func_array($method, $parameters);
                }
            }
        }
    }

    /**
     * Wrap text into specified with.
     *
     * @param array $array  The result
     * @param string $text  The text to wrap
     * @param int $size  Wrap size
     * @param int $ident  Ident size
     */
    protected function wrapText(&$array, $text, $size, $ident = 5)
    {
        $len = $size - $ident;
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            if (trim($line) == '') {
                $array[] = '';
                continue;
            }
            while (true) {
                if (0 === strlen($line)) {
                    break;
                }
                $part = substr($line, 0, $len);
                if ($part !== $line) {
                    // split line break
                    if (false !== ($pos = strrpos($part, ' '))) {
                        $part = substr($part, 0, $pos);
                    }
                }
                $array[] = str_repeat(' ', $ident).rtrim($part);
                $line = ltrim(substr($line, strlen($part)));
            }
        }
    }

    /**
     * Dump all registered script functions.
     *
     * @param string $module  The module name
     * @param int $size  Column size
     * @return array
     */
    public function dump($module = null, $size = 80)
    {
        $result = array();
        $result[] = str_repeat('*', $size);
        $result[] = 'NTScript version '.self::VERSION;
        $result[] = str_repeat('*', $size);
        $result[] = '';
        $result[] = 'Available functions:';
        $result[] = '';
        $count = 0;
        foreach ($this->modules as $mod) {
            if (null !== $module && $mod->getId() !== $module) {
                continue;
            }
            $result[] = str_repeat('-', $size);
            $result[] = sprintf('%2$s (%1$s)', $mod->getId(), $mod->getDescription());
            $result[] = str_repeat('-', $size);
            foreach ($this->functions as $name => $func) {
                if ($func->getModule() !== $mod) {
                    continue;
                }
                $result[] = Script::FUNCTION_IDENTIFIER.$name.Script::FUNCTION_PARAM_START.$func->getSyntax().Script::FUNCTION_PARAM_END;
                if ($func->getDescription()) {
                    $this->wrapText($result, $func->getDescription(), $size);
                }
                $result[] = '';
                $count++;
            }
            $result[] = '';
        }
        $result[] = 'Total functions: '.$count;

        return $result;
    }
}