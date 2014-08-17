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

namespace NTLAB\Script\Context;

class ObjectContext implements ContextInterface
{
    /**
     * @var \NTLAB\Script\Context\ObjectContext
     */
    protected static $instance = null;

    /**
     * Get instance.
     *
     * @return \NTLAB\Script\Context\ObjectContext
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
        return is_object($context);
    }

    /**
     * (non-PHPdoc)
     * @see \NTLAB\Script\Context\ContextInterface::getMethod()
     */
    public function getMethod($context, $name)
    {
        if ($this->canHandle($context)) {
            return array($context, 'get'.$name);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \NTLAB\Script\Context\ContextInterface::getKeyValuePair()
     */
    public function getKeyValuePair($context)
    {
        if (method_exists($context, 'getId')) {
            return array($context->getId(), (string) $context);
        }
    }
}