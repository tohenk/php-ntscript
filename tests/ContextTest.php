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
