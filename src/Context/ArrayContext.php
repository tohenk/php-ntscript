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

namespace NTLAB\Script\Context;

class ArrayContext extends Context implements ContextInterface
{
    /**
     * @var array
     */
    protected $contexes = [];

    /**
     * @var \NTLAB\Script\Context\ArrayVar[]
     */
    protected $caches = [];

    /**
     * @var \NTLAB\Script\Context\ArrayContext
     */
    protected static $instance = null;

    /**
     * Get instance.
     *
     * @return \NTLAB\Script\Context\ArrayContext
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * (non-PHPdoc)
     * @see \NTLAB\Script\Context\ContextInterface::canHandle()
     */
    public function canHandle($context)
    {
        return ($context instanceof ArrayVar) || (!is_object($context) && is_array($context));
    }

    /**
     * (non-PHPdoc)
     * @see \NTLAB\Script\Context\ContextInterface::getMethod()
     */
    public function getMethod($context, $name)
    {
        if ($this->canHandle($context) && $this->isVar($name) && $cache = $this->getCache($context)) {
            return [$cache, 'get'.$name];
        }
    }

    /**
     * (non-PHPdoc)
     * @see \NTLAB\Script\Context\ContextInterface::setMethod()
     */
    public function setMethod($context, $name)
    {
        // do nothing
    }

    /**
     * Get context cache.
     *
     * @param mixed $context  The context
     * @return \NTLAB\Script\Context\ArrayVar
     */
    protected function getCache($context)
    {
        if (!in_array($context, $this->contexes)) {
            $this->contexes[] = $context;
            $this->caches[] = $context instanceof ArrayVar ? $context : new ArrayVar($context);
        }
        if (false !== ($key = array_search($context, $this->contexes))) {
            return $this->caches[$key];
        }
    }

    /**
     * (non-PHPdoc)
     * @see \NTLAB\Script\Context\ContextInterface::getKeyValuePair()
     */
    public function getKeyValuePair($context)
    {
        $vars = $context instanceof ArrayVar ? $context->getVars() : $context;
        if (count($keys = array_keys($vars))) {
            $key = array_shift($keys);
            $values = [];
            if (empty($keys)) {
                $values[] = $vars[$key];
            } else {
                foreach ($keys as $k) {
                    $values[] = $vars[$k];
                }
            }

            return [$vars[$key], implode(' - ', $values)];
        }
    }

    /**
     * (non-PHPdoc)
     * @see \NTLAB\Script\Context\ContextInterface::flush()
     */
    public function flush($context)
    {
        // do nothing
    }
}
