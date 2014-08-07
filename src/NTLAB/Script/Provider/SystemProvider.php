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

namespace NTLAB\Script\Provider;

use NTLAB\Script\Core\Manager;
use NTLAB\Script\Module\SysCore;
use NTLAB\Script\Module\SysLogic;
use NTLAB\Script\Module\SysStack;
use NTLAB\Script\Module\SysMath;
use NTLAB\Script\Module\SysString;
use NTLAB\Script\Module\SysDate;
use NTLAB\Script\Module\SysArray;
use NTLAB\Script\Module\SysCounter;

class SystemProvider implements ProviderInterface
{
    /**
     * @var \NTLAB\Script\Module\Module[]
     */
    protected $modules = null;

    /**
     * @var \NTLAB\Script\Provider\SystemProvider
     */
    protected static $instance = null;

    /**
     * Get instance.
     *
     * @return \NTLAB\Script\Provider\SystemProvider
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->modules = array(
            new SysCore(),
            new SysLogic(),
            new SysStack(),
            new SysMath(),
            new SysString(),
            new SysDate(),
            new SysArray(),
            new SysCounter(),
        );
    }

    /**
     * (non-PHPdoc)
     * @see \NTLAB\Script\Provider\ProviderInterface::getModules()
     */
    public function getModules()
    {
        return $this->modules;
    }
}