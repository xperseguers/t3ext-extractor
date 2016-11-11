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
 * Duration utility class.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Duration
{
    /**
     * Normalizes a duration as a number of seconds.
     *
     * @param string $str
     * @return string
     */
    public static function normalize($str)
    {
        if (preg_match('/^(\d+):(\d+):(\d+)/', $str, $matches)) {
            $hours = (int)$matches[1];
            $minutes = (int)$matches[2];
            $seconds = (int)$matches[3];
            return $hours * 3600 + $minutes * 60 + $seconds;
        }
        return null;
    }

    /**
     * Converts milliseconds to seconds (integer value).
     *
     * @param string $str
     * @return int
     */
    public static function millisecondsToSeconds($str)
    {
        $milliseconds = (int)$str;
        return (int)($milliseconds / 1000);
    }
}
