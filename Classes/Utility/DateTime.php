<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\Extractor\Utility;

/**
 * Date/Time utility class.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class DateTime
{
    /**
     * Converts a date/time into its Unix timestamp.
     *
     * @param string|array|null $str
     * @return int|null
     */
    public static function timestamp($str = null): ?int
    {
        if (is_array($str)) {
            $str = implode(' ', $str);
        }
        if (!is_string($str)) {
            return null;
        }
        if (preg_match('/^\d{4}:\d{2}:\d{2} \d{2}:\d{2}:\d{2}$/', $str)) {
            // PHP built-in format when reading EXIF
            list($date, $time) = explode(' ', $str, 2);
            $str = str_replace(':', '/', $date) . ' ' . $time;
        } elseif (preg_match('/D:(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', $str, $matches)) {
            // Native PDF format
            array_shift($matches);
            $str = vsprintf('%s/%s/%s %s:%s:%s', $matches);
        } elseif (preg_match('/^\d{4}$/', $str)) {
            // Standalone year (prevent 0 with 1 sec added if year is 1970)
            $str .= '/01/01 00:00:01';
        }
        $timestamp = strtotime($str);
        return ($timestamp !== false && $timestamp >= 0) ? $timestamp : null;
    }
}
