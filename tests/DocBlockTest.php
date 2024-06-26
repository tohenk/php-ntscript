<?php

/*
 * The MIT License
 *
 * Copyright (c) 2014-2024 Toha <tohenk@yahoo.com>
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

namespace NTLAB\Script\Test;

use NTLAB\Script\Util\DocBlock;

class DocBlockTest extends BaseTest
{
    /**
     * @var \ReflectionClass
     */
    protected $reflection;

    protected function setUp(): void
    {
        $this->reflection = new \ReflectionClass(get_class($this));
    }

    /**
     *
     * Doc block only description.
     *
     * This is the long description.
     *
     */
    protected function docBlockDesc()
    {
    }

    /**
     * This is a description text.
     *
     * Each description text will be concatenated using space.
     * Separate with blank line to add line break.
     *
     * @param string $tag  Test tag
     *                     which can be written as multiline
     * @return void nothing
     * @other Testing 
     */
    protected function docBlockFull()
    {
    }

    /**
     * Get doc block.
     *
     * @param string $method  Method name
     * @return \NTLAB\Script\Util\DocBlock
     */
    protected function getDocBlock($method)
    {
        return DocBlock::create($this->reflection->getMethod($method)->getDocComment());
    }

    public function testDesc()
    {
        $docDesc = $this->getDocBlock('docBlockDesc');
        $this->assertEquals('Doc block only description.', $docDesc->getBriefDescription());
        $this->assertEquals("Doc block only description.\nThis is the long description.", $docDesc->getDescription());
        $this->assertEquals(0, count($docDesc->getTags()));
    }

    public function testFull()
    {
        $docFull = $this->getDocBlock('docBlockFull');
        $params = $docFull->getNamedTags('@param');
        $tag = $docFull->getParamTag('tag');
        $this->assertEquals('This is a description text.', $docFull->getBriefDescription());
        $this->assertEquals("This is a description text.\nEach description text will be concatenated using space. Separate with blank line to add line break.", $docFull->getDescription());
        $this->assertEquals(3, count($docFull->getTags()));
        $this->assertEquals(1, count($docFull->getNamedTags('@other')));
        $this->assertEquals('string $tag  Test tag which can be written as multiline', $params[0]['data']);
        $this->assertEquals('string', $tag['type']);
        $this->assertEquals('Test tag which can be written as multiline', $tag['desc']);
    }
}