<?php

namespace NTLAB\Script\Test;

use NTLAB\Script\Util\DocBlock;

class DocBlockTest extends BaseTest
{
    /**
     * @var \ReflectionClass
     */
    protected $reflection;

    protected function setUp()
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