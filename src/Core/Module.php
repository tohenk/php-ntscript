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

use NTLAB\Script\Util\DocBlock;

class Module
{
    /**
     * @var string
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $description = null;

    /**
     * @var \NTLAB\Script\Core\Manager
     */
    protected $manager = null;

    /**
     * @var \NTLAB\Script\Core\Script
     */
    protected $script = null;

    /**
     * @var \ReflectionClass
     */
    protected $reflection = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->load();
        $this->configure();
    }

    /**
     * Configure script using doc block.
     */
    protected function load()
    {
        $this->reflection = new \ReflectionClass(get_class($this));
        if ($docBlock = DocBlock::create($this->reflection->getDocComment())) {
            // module id
            if (count($tags = $docBlock->getNamedTags('id'))) {
                $this->id = $tags[0]['data'];
            }
            // module description
            if ($description = $docBlock->getBriefDescription()) {
                if ('.' === substr($description, - 1)) {
                    $description = substr($description, 0, strlen($description) - 1);
                }
                $this->description = $description;
            }
        }
    }

    /**
     * Configure script module.
     */
    protected function configure()
    {
    }

    /**
     * Ensure value is properly converted to string.
     *
     * @param mixed $value
     * @return string
     */
    protected function expectString($value)
    {
        if (null !== $value) {
            if ($value instanceof \DateTime) {
                $value = $value->format(\DateTime::ISO8601);
            }
        }
        return (string) $value;
    }

    /**
     * Get module id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get module description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * Set script manager.
     *
     * @param \NTLAB\Script\Core\Manager $manager  Script manager
     * @return \NTLAB\Script\Core\Module
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
        return $this;
    }

    /**
     * Get currently executing script.
     *
     * @return \NTLAB\Script\Core\Script
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * Set currently executing script.
     *
     * @param \NTLAB\Script\Core\Script $script  Active script
     * @return \NTLAB\Script\Core\Module
     */
    public function setScript(Script $script)
    {
        $this->script = $script;
        return $this;
    }

    /**
     * Register functions.
     *
     * @return \NTLAB\Script\Core\Module
     */
    public function register()
    {
        if ($this->reflection) {
            foreach ($this->reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                // only method begin with f_ considered as script function
                if ('f_' !== substr($method->getName(), 0, 2)) {
                    continue;
                }
                // only those function with doc block only and has tag 'func' considered as script function
                if (!($docBlock = DocBlock::create($method->getDocComment())) || 0 === count($tags = $docBlock->getNamedTags('func'))) {
                    continue;
                }
                // function name and alias
                $funcs = explode(' ', $tags[0]['data']);
                $fname = array_shift($funcs);
                $parameters = ['name' => $fname];
                if (count($funcs)) {
                    $parameters['alias'] = $funcs[0];
                }
                // function description
                $parameters['description'] = $docBlock->getRawDescription();
                // function syntax and parameter
                $cnt = 0;
                $syntax = [];
                foreach ($method->getParameters() as $param) {
                    if (!$param->isOptional()) {
                        $cnt++;
                    }
                    if ($param->isDefaultValueAvailable()) {
                        $syntax[] = sprintf('%s = %s', $param->getName(), var_export($param->getDefaultValue(), true));
                    } else {
                        $syntax[] = $param->getName();
                    }
                }
                // no parameters found, try from doc block
                if (empty($syntax)) {
                    foreach ($docBlock->getNamedTags('param') as $param) {
                        if (null === ($params = $docBlock->splitParam($param['data']))) {
                            continue;
                        }
                        $syntax[] = $params['name'];
                        // set the parameter count as minimum of 1
                        if (0 === $cnt) {
                            $cnt++;
                        }
                    }
                }
                $parameters['param_cnt'] = $cnt;
                $parameters['syntax'] = implode(', ', $syntax);
                if (count($docBlock->getNamedTags('logic'))) {
                    $parameters['logic'] = true;
                }
                // function handler
                $parameters['method'] = [$this, $method->getName()];
                $func = new Func($this, $parameters);
                $this->getManager()->add($func);
            }
        }
        return $this;
    }
}