<?php

namespace NTLAB\Script\Test;

use NTLAB\Script\Parser\Parser;

abstract class ParserBaseTest extends BaseTest
{
    /**
     * @var \NTLAB\Script\Parser\Parser
     */
    protected $parser;

    protected function parseTest()
    {
        $this->parseVars();
        $this->parseFuncs();
    }

    protected function parseVars()
    {
        $this->parser->parse('#func("$test9 it", 0, #a(\'b c d\', $e.F));');
        $this->assertEquals($this->parser->getVariables(), array('test9', 'e.F'), '->parse() proper parse variables from script');
    }

    protected function parseFuncs()
    {
        $this->parser->parse('#func("$test9 it", 0, #a(\'b c d\', $e.F));');
        $this->assertEquals(
            $this->parser->getFunctions(),
            array(
                array('name' => 'func', 'match' => '#func("$test9 it", 0, #a(\'b c d\', $e.F));', 'params' => array('$test9 it', '0', '#a(\'b c d\', $e.F)')),
                array('name' => 'a',    'match' => '#a(\'b c d\', $e.F)',                         'params' => array('b c d', '$e.F')),
            ),
            '->parse() proper parse functions from script'
        );

        $this->parser->parse('#test(a,b)#test2(c,d)');
        $this->assertEquals(
            $this->parser->getFunctions(),
            array(
                array('name' => 'test',  'match' => '#test(a,b)',  'params' => array('a', 'b')),
                array('name' => 'test2', 'match' => '#test2(c,d)', 'params' => array('c', 'd')),
            ),
            '->parse() proper parse combined functions from script'
        );

        $this->parser->parse('#test(\'"test"\',"\'test\'")');
        $this->assertEquals(
            $this->parser->getFunctions(),
            array(
                array('name' => 'test',  'match' => '#test(\'"test"\',"\'test\'")',  'params' => array('"test"', '\'test\'')),
            ),
            '->parse() proper parse functions with double quoted parameter'
        );

        $this->parser->parse('#cdups(#spaceconcat(#beauty(#pvar(SomeObject.OtherObject.OtherVar)),#pvar(MyObject.MyVar)))');
        $this->assertEquals(
            $this->parser->getFunctions(),
            array(
                array(
                    'name'    => 'cdups',
                    'match'   => '#cdups(#spaceconcat(#beauty(#pvar(SomeObject.OtherObject.OtherVar)),#pvar(MyObject.MyVar)))',
                    'params'  => array('#spaceconcat(#beauty(#pvar(SomeObject.OtherObject.OtherVar)),#pvar(MyObject.MyVar))'),
                ),
                array(
                    'name'    => 'spaceconcat',
                    'match'   => '#spaceconcat(#beauty(#pvar(SomeObject.OtherObject.OtherVar)),#pvar(MyObject.MyVar))',
                    'params'  => array('#beauty(#pvar(SomeObject.OtherObject.OtherVar))', '#pvar(MyObject.MyVar)'),
                ),
                array(
                    'name'    => 'beauty',
                    'match'   => '#beauty(#pvar(SomeObject.OtherObject.OtherVar))',
                    'params'  => array('#pvar(SomeObject.OtherObject.OtherVar)'),
                ),
                array(
                    'name'    => 'pvar',
                    'match'   => '#pvar(SomeObject.OtherObject.OtherVar)',
                    'params'  => array('SomeObject.OtherObject.OtherVar'),
                ),
                array(
                    'name'    => 'pvar',
                    'match'   => '#pvar(MyObject.MyVar)',
                    'params'  => array('MyObject.MyVar'),
                ),
            ),
            '->parse() proper parse complex functions from script'
        );
    }
}