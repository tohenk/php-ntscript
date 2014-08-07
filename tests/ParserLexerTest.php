<?php

namespace NTLAB\Script\Test;

use NTLAB\Script\Parser\LexerParser;

class ParserLexerTest extends ParserBaseTest
{
    protected function setUp()
    {
        $this->parser = new LexerParser();
    }

    public function testParser()
    {
        $this->parseTest();
    }
}