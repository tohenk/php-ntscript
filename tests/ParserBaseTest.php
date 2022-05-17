<?php

namespace NTLAB\Script\Test;

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
        $this->parser->parse('$var');
        $this->assertEquals(['var'], $this->parser->getVariables(), '->parse() proper parse simple variable from script');
        $this->parser->parse('#func("$test9 it", 0, #a(\'b c d\', $e.F));');
        $this->assertEquals(['test9', 'e.F'], $this->parser->getVariables(), '->parse() proper parse variables from script');
    }

    protected function parseFuncs()
    {
        $this->parser->parse('#func("$test9 it", 0, #a(\'b c d\', $e.F));');
        $this->assertEquals(
            [
                ['name' => 'func', 'match' => '#func("$test9 it", 0, #a(\'b c d\', $e.F));', 'params' => ['$test9 it', '0', '#a(\'b c d\', $e.F)']],
                ['name' => 'a',    'match' => '#a(\'b c d\', $e.F)',                         'params' => ['b c d', '$e.F']],
            ],
            $this->parser->getFunctions(),
            '->parse() proper parse functions from script'
        );

        $this->parser->parse('#test(a,b)#test2(c,d)');
        $this->assertEquals(
            [
                ['name' => 'test',  'match' => '#test(a,b)',  'params' => ['a', 'b']],
                ['name' => 'test2', 'match' => '#test2(c,d)', 'params' => ['c', 'd']],
            ],
            $this->parser->getFunctions(),
            '->parse() proper parse combined functions from script'
        );

        $this->parser->parse('#test(\'"$var"\',"\'test\'")');
        $this->assertEquals(
            [
                ['name' => 'test',  'match' => '#test(\'"$var"\',"\'test\'")',  'params' => ['"$var"', '\'test\'']],
            ],
            $this->parser->getFunctions(),
            '->parse() proper parse functions with double quoted parameter'
        );

        $this->parser->parse('#test("Testing #test(\'#func(test,me)\',\'me\')")');
        $this->assertEquals(
            [
                ['name' => 'test',  'match' => '#test("Testing #test(\'#func(test,me)\',\'me\')")', 'params' => ['Testing #test(\'#func(test,me)\',\'me\')']],
                ['name' => 'test',  'match' => '#test(\'#func(test,me)\',\'me\')',  'params' => ['#func(test,me)', 'me']],
                ['name' => 'func',  'match' => '#func(test,me)',  'params' => ['test', 'me']],
            ],
            $this->parser->getFunctions(),
            '->parse() proper parse functions with combined quote parameter'
        );

        $this->parser->parse('#cdups(#spaceconcat(#beauty(#pvar(SomeObject.OtherObject.OtherVar)),#pvar(MyObject.MyVar)))');
        $this->assertEquals(
            [
                [
                    'name'    => 'cdups',
                    'match'   => '#cdups(#spaceconcat(#beauty(#pvar(SomeObject.OtherObject.OtherVar)),#pvar(MyObject.MyVar)))',
                    'params'  => ['#spaceconcat(#beauty(#pvar(SomeObject.OtherObject.OtherVar)),#pvar(MyObject.MyVar))'],
                ],
                [
                    'name'    => 'spaceconcat',
                    'match'   => '#spaceconcat(#beauty(#pvar(SomeObject.OtherObject.OtherVar)),#pvar(MyObject.MyVar))',
                    'params'  => ['#beauty(#pvar(SomeObject.OtherObject.OtherVar))', '#pvar(MyObject.MyVar)'],
                ],
                [
                    'name'    => 'beauty',
                    'match'   => '#beauty(#pvar(SomeObject.OtherObject.OtherVar))',
                    'params'  => ['#pvar(SomeObject.OtherObject.OtherVar)'],
                ],
                [
                    'name'    => 'pvar',
                    'match'   => '#pvar(SomeObject.OtherObject.OtherVar)',
                    'params'  => ['SomeObject.OtherObject.OtherVar'],
                ],
                [
                    'name'    => 'pvar',
                    'match'   => '#pvar(MyObject.MyVar)',
                    'params'  => ['MyObject.MyVar'],
                ],
            ],
            $this->parser->getFunctions(),
            '->parse() proper parse complex functions from script'
        );
    }
}