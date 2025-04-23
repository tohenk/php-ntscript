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

namespace NTLAB\Script\Test;

use PHPUnit\Framework\TestCase;
use NTLAB\Script\Context\ArrayVar;
use NTLAB\Script\Context\ContextIterator;
use NTLAB\Script\Context\PartialObject;
use NTLAB\Script\Core\Manager as ScriptManager;
use NTLAB\Script\Core\Module;
use NTLAB\Script\Core\Script;
use NTLAB\Script\Listener\ListenerInterface;

class ScriptTest extends TestCase
{
    /**
     * @var \NTLAB\Script\Core\Script
     */
    protected $script;

    protected $modules = [
        'system.array' => ['each', 'lcreate', 'ladd', 'lconcat'],
        'system.context' => ['recno', 'reccnt'],
        'system.core' => ['func', 'var', 'null', 'eval', 'const'],
        'system.counter' => ['cget', 'cset', 'creset', 'cinc', 'cdec', 'series'],
        'system.date' => ['fmtdate', 'dtafter', 'dtbefore', 'dtpart', 'time'],
        'system.logic' => ['if', 'cmp', 'eq', 'neq', 'leq', 'geq', 'ls', 'gr', 'and', 'or', 'not', 'isnull'],
        'system.math' => ['sum', 'sub', 'mul', 'div', 'mod', 'inc', 'dec', 'int', 'numonly'],
        'system.stack' => ['sclr', 'sexist', 'spush', 'spop'],
        'system.string' => ['split', 'ucfirst', 'ucwords', 'upper', 'lower', 'trim', 'concat', 'concatw', 'concatall', 'spaceconcat', 'repeat', 'pos', 'strpos', 'len', 'ch', 'space', 'crlf', 'splitdel', 'left', 'right', 'substr', 'p', 'q', 'empty', 'notempty'],
        'test.core' => ['callme', 'callres', 'test'],
    ];

    protected function setUp(): void
    {
        $this->script = new Script();
    }

    public function testModule()
    {
        foreach (array_keys($this->modules) as $module) {
            $this->assertNotNull($this->script->getManager()->getModule($module), sprintf('Module %s is exist', $module));
        }
    }

    public function testFunctions()
    {
        foreach ($this->modules as $functions) {
            foreach ($functions as $func) {
                $this->assertTrue($this->script->getManager()->has($func), sprintf('Function %s is exist', $func));
            }
        }
    }

    public function testAlias()
    {
        $this->script->getManager()->addAlias('somefunc', 'test');
        $this->script->getManager()->addAlias('somefunc2', 'function_not_exist');
        $this->assertTrue($this->script->getManager()->has('somefunc'), 'Function alias successfuly added if target function exist');
        $this->assertFalse($this->script->getManager()->has('somefunc2'), 'Function alias not added if target function doesn\'t exist');
    }

    protected function assertContext($context)
    {
        $this->script->setContext($context);
        $this->assertEquals('something', $this->script->evaluate('$var'), '->evaluate() proper replace variable');
        $this->assertEquals('VAR3', $this->script->evaluate('$VAR3'), '->evaluate() proper replace uppercased variable');
        $this->assertEquals('something', $this->script->evaluate('$var2.test'), '->evaluate() proper replace variable with context');
        $this->assertEquals('TEST1', $this->script->evaluate('$var2.test1'), '->evaluate() proper replace variable with __call()');
        $this->assertEquals('TEST2', $this->script->evaluate('$var2.TEST2'), '->evaluate() proper replace uppercased variable with __call()');
        $this->assertEquals(null, $this->script->evaluate('$notexist'), '->evaluate() proper replace variable which may throw exception');
    }

    public function testNonScript()
    {
        $this->assertEquals('something', $this->script->evaluate('something'), '->evaluate() proper evaluate non script');
        $this->assertEquals('123', $this->script->evaluate('123'), '->evaluate() proper evaluate non script');
        $this->assertEquals('this text is indeed not long.', $this->script->evaluate('this text is indeed not long.'), '->evaluate() proper evaluate non script');
    }

    public function testArrayContext()
    {
        $this->assertContext(['var' => 'something', 'Var2' => new TestContext2(), 'VAR3' => 'VAR3']);
    }

    public function testObjectContext()
    {
        $this->assertContext(new TestContext());
    }

    public function testScript()
    {
        $this->script->setContext(new TestContext());
        $this->assertEquals('#test(a,b)#test2(c,d)', $this->script->evaluate('#func(test,a,b)#func(test2,c,d)'), '->evaluate() proper evaluate combined script');
        $this->assertEquals('TESTCALLCALL', $this->script->evaluate('#callme()#test()#callme()#callres()'), '->evaluate() proper evaluate script in ordered sequence');
        $this->assertEquals("\rA\rB", $this->script->evaluate('#ch(13)#concatw(#ch(13),"A","B")'), '->evaluate() proper evaluate script in ordered sequence 2');
        $this->assertEquals('4', $this->script->evaluate('#len(#test())'), '->evaluate() proper evaluate nested script with no parameter');
        $this->assertEquals('4', $this->script->evaluate('#len("#test()")'), '->evaluate() proper evaluate nested script with no parameter in quote');
        $this->assertEquals('23', $this->script->evaluate('#len("something(with) to #test()")'), '->evaluate() proper evaluate script with parenthesis inside');
        $this->assertEquals('#test($var)', $this->script->evaluate('#func(test,#var(var))'), '->evaluate() proper parse script');
        $this->assertEquals('9', $this->script->evaluate('#len($var)'), '->evaluate() proper parse script');
        $this->assertEquals('4', $this->script->evaluate('#eval("1 + 3")'), '->evaluate() return eval function');

        $this->assertEquals('ME', $this->script->evaluate('#if(1,"ME","YOU")'), '->evaluate() proper parse if when true');
        $this->assertEquals('YOU', $this->script->evaluate('#if(0,"ME","YOU")'), '->evaluate() proper parse if when false');
        $this->assertEquals(null, $this->script->evaluate('#if(0,"ME")'), '->evaluate() proper parse if when false with optional value');
        $this->assertEquals('YES', $this->script->evaluate('#if(#gr(2,1),"YES")'), '->evaluate() proper parse if when condition is function');
        $this->assertEquals('9 test: something', $this->script->evaluate('#if(1,"#len($var) test: $var")'), '->evaluate() proper parse if with special chars');
        $this->assertEquals('1', $this->script->evaluate('#isnull(#null())'), '->evaluate() proper check for null');

        $this->assertEquals('X1, X2, X3', $this->script->evaluate('#lcreate(x);#each($var5,"#func(ladd,x,#var(var))");#lconcat(x,", ")'), '->evaluate() proper process each and list');

        $this->assertEquals(<<<EOF
First if: it's something
Second if: it's something too
EOF
            , $this->script->evaluate(
                <<<EOF
First if: #if(#eq(\$var,something),"it's something","it's not something")
Second if: #if(#eq(\$var,something),"it's something too","no, it's not something")
EOF
            ), '->evaluate() proper process multiple if occurance with same condition');
    }

    public function testIterator()
    {
        $seq = 0;
        $values = ['A1', 'A2', 'A3'];
        $objects = array_map(fn ($a) => new TestContext2($a), $values);
        $this->script
            ->setObjects($objects)
            ->each(function (Script $script, ScriptTest $_this) use ($values, &$seq) {
                $seq++;
                $_this->assertEquals($values[$seq - 1], $script->evaluate('$Var'), sprintf('Variable is %s', $values[$seq - 1]));
                $_this->assertEquals($seq, $script->evaluate('#recno()'), sprintf('Record number #%d', $seq));
            })
        ;
        $this->assertEquals(3, $this->script->evaluate('#reccnt()'), 'Record count should be 3');
    }

    public function testPartial()
    {
        $seq = 5;
        $idx = 0;
        $total = 10;
        $values = ['B1', 'B2', 'B3'];
        $objects = array_map(fn ($a) => new TestContext2($a), $values);
        $this->script
            ->setObjects(new PartialObject($objects, $seq, $total))
            ->each(function (Script $script, ScriptTest $_this) use ($values, &$seq, &$idx) {
                $seq++;
                $_this->assertEquals($values[$idx], $script->evaluate('$Var'), sprintf('Variable is %s', $values[$idx]));
                $_this->assertEquals($seq, $script->evaluate('#recno()'), sprintf('Record number #%d', $seq));
                $idx++;
            })
        ;
        $this->assertEquals(10, $this->script->evaluate('#reccnt()'), 'Record count should be 10');
    }
}

class TestContext
{
    protected $var2;
    protected $var4;
    protected $var5;

    public function __construct()
    {
        $this->var2 = new TestContext2();
        $this->var4 = [
            new TestContext2('X1'),
            new TestContext2('X2'),
            new TestContext2('X3'),
        ];
        $this->var5 = new \ArrayObject();
        $this->var5->append(new TestContext2('X1'));
        $this->var5->append(new TestContext2('X2'));
        $this->var5->append(new TestContext2('X3'));
    }

    public function getVar()
    {
        return 'something';
    }

    public function getVar2()
    {
        return $this->var2;
    }

    public function getVAR3()
    {
        return 'VAR3';
    }

    public function getVar4()
    {
        return $this->var4;
    }

    public function getVar5()
    {
        return $this->var5;
    }
}

class TestContext2 extends ArrayVar
{
    protected $var = null;

    public function __construct($var = null)
    {
        $this->var = $var;
        parent::__construct([
            'test1' => 'TEST1',
            'TEST2' => 'TEST2',
        ]);
    }

    public function getTest()
    {
        return 'something';
    }

    public function getVar()
    {
        return $this->var;
    }
}

/**
 * Test functions.
 *
 * @author Toha
 * @id test.core
 */
class TestModule extends Module
{
    protected $calls = [];

    /**
     * Call me function.
     * @func callme
    */
    public function f_CallMe()
    {
        $this->calls[] = 'CALL';
    }

    /**
     * Call me result function.
     *
     * @return string
     * @func callres
     */
    public function f_CallResult()
    {
        return implode('', $this->calls);
    }

    /**
     * Test function.
     *
     * @return string
     * @func test
     */
    public function f_Test()
    {
        return 'TEST';
    }
}

class TestListener implements ListenerInterface
{
    protected static $instance = null;

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function notifyModuleRegister($manager)
    {
        $manager->addModule(new TestModule());
    }

    public function notifyContextChange($context, ContextIterator $iterator)
    {
    }
}

// register test module
ScriptManager::addListener(TestListener::getInstance());
