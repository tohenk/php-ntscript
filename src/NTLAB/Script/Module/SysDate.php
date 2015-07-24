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

/**
 * Date functions.
 *
 * @author Toha
 * @id system.date
 */
class SysDate extends Module
{
    /**
     * Get date time.
     *
     * @param string $date  Date time
     * @return \DateTime
     */
    protected function getDate($date)
    {
        $time = null;
        // check if date is a timestamp
        if (is_numeric($date)) {
            $time = (int) $date;
        } else {
            $time = @strtotime($date);
        }
        if (null !== $time) {
            $dt = new \DateTime();
            $dt->setTimestamp($time);

            return $dt;
        }
    }

    /**
     * Format date using PHP builtin convension.
     *
     * @param mixed $date  The date time to format
     * @param string $format  The date time format
     * @return string
     * @func fmtdate
     */
    public function f_DateFormat($date, $format = 'd-m-Y')
    {
        if ($date = $this->getDate($date)) {
            return $date->format($format);
        }
    }

    /**
     * Get the next date after the `days` days.
     *
     * @param mixed $date  The date time
     * @param int $days  The days to add
     * @return string
     * @func dtafter
     */
    public function f_DateAfter($date, $days)
    {
        try {
            if ($date = $this->getDate($date)) {
                $date->modify(sprintf('+%d day', (int) $days));

                return $date->format('Y-m-d');
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * Get the prior date before the `days` days.
     *
     * @param mixed $date  The date time
     * @param int $days  The days to add
     * @return string
     * @func dtbefore
     */
    public function f_DateBefore($date, $days)
    {
        try {
            if ($date = $this->getDate($date)) {
                $date->modify(sprintf('-%d day', (int) $days));

                return $date->format('Y-m-d');
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * Get a part of the date value.
     *
     * The part can be of `d` for day, `m` for month, and `y` or `Y` for year.
     *
     * @param string $date  The date value
     * @param string $part  The part
     * @return string
     * @func dtpart
     */
    public function f_DatePart($date, $part)
    {
        if ($date = @strtotime($date)) {
            switch ($part) {
                case 'd':
                case 1:
                    return date('d', $date);
                    break;

                case 'm':
                case 2:
                    return date('m', $date);
                    break;

                case 'Y':
                case 'y':
                case 3:
                    return date('Y', $date);
                    break;
            }
        }
    }

    /**
     * Get the current system date and time.
     *
     * @return int
     * @func time
     */
    public function f_Time()
    {
        return time();
    }
}