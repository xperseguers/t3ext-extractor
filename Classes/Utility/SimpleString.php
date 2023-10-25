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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * String utility class.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class SimpleString
{
    /**
     * Trims a string (and also removes non-printable binary characters).
     *
     * @param string|null $str
     * @return string|null
     */
    public static function trim(?string $str = null): ?string
    {
        if (empty($str)) {
            return $str;
        }

        // Remove non-printable characters (ASCII 0-31)
        $str = preg_replace('/[\x00-\x1F]/', '', $str);
        return trim($str);
    }


    /**
     * Returns true if the passed $haystack starts from the $needle string or false otherwise.
     *
     * @internal Calls `str_starts_with` on modern TYPO3 installations (TYPO3 10.4 or newer / PHP 8.0 or newer),
     * falls back to GeneralUtility::isFirstPartOfStr() on legacy TYPO3 installations.
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function isFirstPartOfStr(string $haystack, string $needle): bool
    {
        return function_exists('str_starts_with') ? str_starts_with($haystack, $needle) : GeneralUtility::isFirstPartOfStr($haystack, $needle);
    }
}
