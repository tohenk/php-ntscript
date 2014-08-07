<?php

namespace NTLAB\Script\Test;

use NTLAB\Script\Parser\ReParser;

abstract class ParserReTest extends ParserBaseTest
{
    protected function setUp()
    {
        $this->parser = new ReParser();
    }

    public function testParser()
    {
        $this->parseTest();
    }
}