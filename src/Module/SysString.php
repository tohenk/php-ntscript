<?php

/*
 * The MIT License
 *
 * Copyright (c) 2014 Toha <tohenk@yahoo.com>
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

namespace NTLAB\Script\Module;

use NTLAB\Script\Core\Module;
use NTLAB\Script\Core\Script;

/**
 * String functions.
 *
 * @author Toha
 * @id system.string
 */
class SysString extends Module
{
    /**
     * Split text `s` every `count` numbers and separate with `separator`.
     * E.g. #split(TEST, 1) will output T E S T.
     *
     * @param string $s  The text
     * @param int $count  Split size
     * @param string $separator  Text separator
     * @return string
     * @func split
     */
    public function f_Split($s, $count, $separator = ' ')
    {
        $value = null;
        $s = $this->expectString($s);
        while (true) {
            if (0 == strlen($s)) {
                break;
            }
            $part = substr($s, 0, $count);
            $s = substr($s, $count);
            if (null !== $value) {
                $value .= $separator;
            }
            $value .= $part;
        }

        return $value;
    }

    /**
     * Capitalize first letter of text.
     *
     * @param string $s  The text
     * @return string
     * @func ucfirst
     */
    public function f_Ucfirst($s)
    {
        return ucfirst($this->expectString($s));
    }

    /**
     * Capitalize first letter of each words.
     *
     * @param string $s  The text
     * @return string
     * @func ucwords
     */
    public function f_Ucwords($s)
    {
        return ucwords($this->expectString($s));
    }

    /**
     * Turn the text into UPPERCASE.
     *
     * @param string $s  The text
     * @return string
     * @func upper
     */
    public function f_Upper($s)
    {
        return strtoupper($this->expectString($s));
    }

    /**
     * Turn the text into lowercase.
     *
     * @param string $s  The text
     * @return string
     * @func lower
     */
    public function f_Lower($s)
    {
        return strtolower($this->expectString($s));
    }

    /**
     * Remove leading and trailing WHITESPACE from text.
     *
     * @param string $s  The text
     * @return string
     * @func trim
     */
    public function f_Trim($s)
    {
        return trim($this->expectString($s));
    }

    /**
     * Concatenate `s1` and `s2` with delimeter `delimeter`.
     *
     * @param string $s1  Text 1
     * @param string $s2  Text 2
     * @param string $delimeter  Text delimeter
     * @return string
     * @func concat
     */
    public function f_Concat($s1, $s2, $delimeter = '')
    {
        return $this->expectString($s1).$this->expectString($delimeter).$this->expectString($s2);
    }

    /**
     * Concatenate all text with delimeter `delimeter`.
     *
     * @param string $delimeter  The text delimeter
     * @param string $str1  Text 1
     * @param string $str2  Text 2
     * @param string $...  Text 3
     * @return string
     * @func concatw
     */
    public function f_ConcatWith()
    {
        $args = func_get_args();
        $delimeter = array_shift($args);
        $value = null;
        foreach ($args as $arg) {
            if ($arg) {
                if (null !== $value) {
                    $value .= $delimeter;
                }
                $value .= $this->expectString($arg);
            }
        }

        return $value;
    }

    /**
     * Concatenate all text.
     *
     * @param string $str1  Text 1
     * @param string $str2  Text 2
     * @param string $...  Text 3
     * @return string
     * @func concatall
     */
    public function f_ConcatAll()
    {
        $args = func_get_args();
        $value = null;
        foreach ($args as $arg) {
            $value .= $this->expectString($arg);
        }

        return $value;
    }

    /**
     * Concatenate all text with SPACE.
     *
     * @param string $str1  Text 1
     * @param string $str2  Text 2
     * @param string $...  Text 3
     * @return string
     * @func spaceconcat
     */
    public function f_SpaceConcat()
    {
        $args = func_get_args();
        array_unshift($args, ' ');

        return call_user_func_array(array($this, 'f_ConcatWith'), $args);
    }

    /**
     * Repeat `s` to `count` times.
     *
     * @param string $s  The text to repeat
     * @param int $count  The repeat time
     * @return string
     * @func repeat
     */
    public function f_Repeat($s, $count)
    {
        return str_repeat($this->expectString($s), max($count, 0));
    }

    /**
     * Return the position of `search` in `s`.
     *
     * @param string $search  The string to search
     * @param string $s  The contained string to search for
     * @return int
     * @func pos
     */
    public function f_Pos($search, $s)
    {
        return strpos($this->expectString($s), $search);
    }

    /**
     * Return the position of `search` in `s`.
     *
     * @param string $s  The contained string to search for
     * @param string $search  The string to search
     * @return int
     * @func strpos
     */
    public function f_Strpos($s, $search)
    {
        return strpos($this->expectString($s), $search);
    }

    /**
     * Return the length of `s`.
     *
     * @param string $s  The text
     * @return int
     * @func len
     */
    public function f_Len($s)
    {
        return strlen($this->expectString($s));
    }

    /**
     * Return the ASCII char of code `code`.
     *
     * @param int $code  The ASCII code
     * @return string
     * @func ch
     */
    public function f_Ch($code)
    {
        return chr($code);
    }

    /**
     * Repeat SPACE `count` times.
     *
     * @param int $count  Repeat times.
     * @return string
     * @func space
     */
    public function f_Space($count)
    {
        return str_repeat(' ', $count);
    }

    /**
     * Return CRLF.
     *
     * @return string
     * @func crlf
     */
    public function f_CrLf()
    {
        return "\r\n";
    }

    /**
     * Split a text by `delim` and return the `element`-th.
     * Element is 0 based.
     *
     * @param string $s  The text to split
     * @param string $delim  The text delimeter
     * @param int $element  The position to retrieve
     * @return string
     * @func splitdel
     */
    public function f_SplitDelimeter($s, $delim, $element)
    {
        $array = explode($this->expectString($delim), $this->expectString($s));

        return isset($array[$element]) ? $array[$element] : null;
    }

    /**
     * Pick `count` chars from text `s` starting from left.
     *
     * @param string $s  The text
     * @param int $count  The number of text to pick
     * @return string
     * @func left
     */
    public function f_Left($s, $count)
    {
        return substr($this->expectString($s), 0, $count);
    }

    /**
     * Pick `count` chars from text `s` starting from right.
     *
     * @param string $s  The text
     * @param int $count  The number of text to pick
     * @return string
     * @func right
     */
    public function f_Right($s, $count)
    {
        return substr($this->expectString($s), - $count);
    }

    /**
     * Pick `count` chars from text `s` starting at position `start`.
     *
     * @param string $s  The text
     * @param int $start  The start position
     * @param int $count  The number of char
     * @return string
     * @func substr
     */
    public function f_Substr($s, $start, $count)
    {
        return substr($this->expectString($s), $start, $count);
    }

    /**
     * Pick first word from `s`.
     *
     * @param string $s  The text
     * @return string
     * @func firstw
     */
    public function f_FirstWord($s)
    {
        if (count($words = explode(' ', $s))) {
            return $words[0];
        }
    }

    /**
     * Pick last word from `s`.
     *
     * @param string $s  The text
     * @return string
     * @func lastw
     */
    public function f_LastWord($s)
    {
        if (count($words = explode(' ', $s))) {
            return $words[count($words) - 1];
        }
    }

    /**
     * Enclose text in parenthesis.
     *
     * @param string $s  The text to enclose
     * @return string
     * @func p
     */
    public function f_Enclose($s = null)
    {
        return sprintf('(%s)', $this->expectString($s));
    }

    /**
     * Quote text using single quote.
     *
     * @param string $s  The text to quote
     * @return string
     * @func q
     */
    public function f_Quote($s = null)
    {
        return sprintf('\'%s\'', $this->expectString($s));
    }

    /**
     * Quote text using double quote.
     *
     * @param string $s  The text to quote
     * @return string
     * @func dq
     */
    public function f_DoubleQuote($s = null)
    {
        return sprintf('"%s"', $this->expectString($s));
    }

    /**
     * Evaluate if text is empty.
     *
     * @param string $s  The text
     * @return int
     * @func empty
     */
    public function f_Empty($s)
    {
        return Script::asBool(strlen($this->expectString($s)) == 0);
    }

    /**
     * Evaluate if text is not empty.
     *
     * @param string $s  The text
     * @return int
     * @func notempty
     */
    public function f_NotEmpty($s)
    {
        return Script::asBool(strlen($this->expectString($s)) > 0);
    }
}