<?php

namespace NTLAB\Script\Test;

use NTLAB\Script\Context\ArrayVar;
use NTLAB\Script\Core\Manager;
use NTLAB\Script\Core\Module;
use NTLAB\Script\Core\Script;
use NTLAB\Script\Listener\ListenerInterface;

class ScriptTest extends BaseTest
{
    /**
     * @var \NTLAB\Script\Core\Script
     */
    protected $script;

    protected $modules = array(
        'system.array'    => array('each', 'eachvar', 'lcreate', 'ladd', 'lconcat'),
        'system.core'     => array('func', 'var', 'null', 'eval', 'const'),
        'system.counter'  => array('cget', 'cset', 'creset', 'cinc', 'cdec', 'series'),
        'system.date'     => array('fmtdate', 'dtafter', 'dtbefore', 'dtpart', 'time'),
        'system.logic'    => array('if', 'cmp', 'eq', 'neq', 'leq', 'geq', 'ls', 'gr', 'and', 'or', 'not'),
        'system.math'     => array('sum', 'sub', 'mul', 'div', 'mod', 'inc', 'dec', 'int', 'numonly'),
        'system.stack'    => array('sclr', 'sexist', 'spush', 'spop'),
        'system.string'   => array('split', 'ucfirst', 'ucwords', 'upper', 'lower', 'trim', 'concat', 'concatw', 'concatall', 'spaceconcat', 'repeat', 'pos', 'strpos', 'len', 'ch', 'space', 'crlf', 'splitdel', 'left', 'right', 'substr', 'p', 'q', 'empty', 'notempty'),
        'test.core'       => array('callme', 'callres', 'test'),
    );

    protected function setUp()
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

    protected function assertContext()
    {
        $this->assertEquals('something', $this->script->evaluate('$var'), '->evaluate() proper replace variable');
        $this->assertEquals('VAR3', $this->script->evaluate('$VAR3'), '->evaluate() proper replace uppercased variable');
        $this->assertEquals('something', $this->script->evaluate('$var2.test'), '->evaluate() proper replace variable with context');
        $this->assertEquals('TEST1', $this->script->evaluate('$var2.test1'), '->evaluate() proper replace variable with __call()');
        $this->assertEquals('TEST2', $this->script->evaluate('$var2.TEST2'), '->evaluate() proper replace uppercased variable with __call()');
        $this->assertEquals(null, $this->script->evaluate('$var2.notexist'), '->evaluate() proper replace variable with __call() with exception');
    }

    public function testContext()
    {
        $array = array('var' => 'something', 'Var2' => new TestContext2(), 'VAR3' => 'VAR3');
        $this->script->setContext($array);
        $this->assertContext();
        $this->script->setContext(new TestContext());
        $this->assertContext();
    }

    public function testScript()
    {
        $this->script->setContext(new TestContext());
        $this->assertEquals('#test(a,b)#test2(c,d)', $this->script->evaluate('#func(test,a,b)#func(test2,c,d)'), '->evaluate() proper evaluate combined script');
        $this->assertEquals('TESTCALLCALL', $this->script->evaluate('#callme()#test()#callme()#callres()'), '->evaluate() proper evaluate script in ordered sequence');
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

        $this->assertEquals('X1, X2, X3', $this->script->evaluate('#lcreate(x);#each($var5,"#func(ladd,x,#func(eachvar,var))");#lconcat(x,", ")'), '->evaluate() proper process each and list');
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
        $this->var4 = array(
            new TestContext2('X1'),
            new TestContext2('X2'),
            new TestContext2('X3'),
        );
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
        parent::__construct(array(
            'test1' => 'TEST1',
            'TEST2' => 'TEST2',
        ));
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
    protected $calls = array();

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
}

// register test module
Manager::addListener(TestListener::getInstance());