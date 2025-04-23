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

namespace NTLAB\Script\Stream;

class Stream
{
    /**
     * Stream content.
     *
     * @var string
     */
    protected $content = null;

    /**
     * Current position.
     *
     * @var int
     */
    protected $pos = null;

    /**
     * Content length.
     *
     * @var int
     */
    protected $len = null;

    /**
     * Current char.
     *
     * @var string
     */
    protected $ch = null;

    /**
     * Character encoding.
     *
     * @var string
     */
    protected $encoding = null;

    /**
     * Create stream object.
     *
     * @param string $content Stream content
     * @return \NTLAB\Script\Stream\Stream
     */
    public static function create($content)
    {
        if ($content instanceof self) {
            return $content;
        }

        return new self($content);
    }

    /**
     * Constructor.
     *
     * @param string $content  Stream content
     */
    public function __construct($content)
    {
        $this->content = $content;
        $this->encoding = mb_detect_encoding($this->content);
        $this->len = mb_strlen($this->content);
        $this->pos = 0;
    }

    /**
     * Read single character.
     *
     * @param boolean $advance  Advance to next position
     * @return boolean
     */
    public function read($advance = true)
    {
        $this->ch = null;
        if ($this->pos < $this->len) {
            $this->ch = mb_substr($this->content, $this->pos, 1, $this->encoding);
            if ($advance) {
                $this->pos++;
            }

            return true;
        }

        return false;
    }

    /**
     * Skip single character if matches input.
     *
     * @param array $chars  Characters to match
     * @return boolean
     */
    public function skip($chars = [])
    {
        $chars = is_array($chars) ? $chars : [$chars];
        if ($this->read()) {
            if (in_array($this->ch, $chars)) {
                return true;
            }
            $this->pos--;
        }

        return false;
    }

    /**
     * Get current position.
     *
     * @return int
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * Get current character.
     *
     * @return string
     */
    public function getChar()
    {
        return $this->ch;
    }

    /**
     * Move to the next position.
     *
     * @return int
     */
    public function next()
    {
        $this->pos++;

        return $this->pos;
    }

    /**
     * Move to the previous position.
     *
     * @return int
     */
    public function prev()
    {
        $this->pos--;

        return $this->pos;
    }

    /**
     * Is stream available.
     *
     * @return boolean
     */
    public function available()
    {
        return ($this->len > 0) && ($this->pos < $this->len);
    }

    /**
     * Check if current character is equal with.
     *
     * @param string $chars
     * @return boolean
     */
    public function is($chars)
    {
        $chars = is_array($chars) ? $chars : [$chars];

        return in_array($this->ch, $chars);
    }

    /**
     * Check if required chars is present.
     *
     * @param string $chars
     * @return boolean
     */
    public function has($chars)
    {
        return $this->read() && $this->is($chars) ? true : false;
    }

    /**
     * Skip whitespaces.
     *
     * @return boolean
     */
    public function skipWhitespace()
    {
        while (true) {
            $result = $this->read();
            if ($result && !preg_match('/\s/x', $this->ch)) {
                $this->pos--;
                break;
            }
        }

        return $result;
    }

    /**
     * Check if current character is digit.
     *
     * @return boolean
     */
    public function isDigit()
    {
        return preg_match('/[0-9]/', $this->ch) ? true : false;
    }

    /**
     * Get the remaining content.
     *
     * @return string
     */
    public function remain()
    {
        return mb_substr($this->content, $this->pos, null, $this->encoding);
    }

    /**
     * Pick part of stream content.
     *
     * @param int $start  Start position
     * @param int $len  The desired length
     * @return string
     */
    public function pick($start, $len)
    {
        return mb_substr($this->content, $start, $len, $this->encoding);
    }
}
