<?php

namespace NTLAB\Script\Test;

use NTLAB\Script\Context\Context;

class ContextTest extends BaseTest
{
    public function testVar()
    {
        $context = new Context();
        $this->assertTrue($context->isVar('myvar'), '->isVar() accepts name in lowercase');
        $this->assertTrue($context->isVar('MYVAR'), '->isVar() accepts name in upercase');
        $this->assertTrue($context->isVar('myVAR'), '->isVar() accepts name with mixed lowercase and upercase');
        $this->assertTrue($context->isVar('myVAR123'), '->isVar() accepts name with mixed lowercase, upercase, and number');
        $this->assertTrue($context->isVar('my_VAR'), '->isVar() accepts name with underscore');
        $this->assertFalse($context->isVar('123myVAR'), '->isVar() rejects name prefixed with number');
        $this->assertFalse($context->isVar('myVAR+-78'), '->isVar() rejects name with non alpha numeric');
    }
}
