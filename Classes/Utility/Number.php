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
 * Number utility class.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Number
{
    /**
     * Casts a string as an integer.
     *
     * @param string $str
     * @return int
     */
    public static function castInteger(?string $str): int
    {
        return (int)$str;
    }

    /**
     * Extracts an integer at the end of a string.
     *
     * @param string $str
     * @return int|null
     */
    public static function extractIntegerAtEnd(?string $str = null): ?int
    {
        if (preg_match('/(\d+)$/', $str, $matches)) {
            return (int)$matches[1];
        }
        return null;
    }

    /**
     * Extract a float from a string.
     *
     * @param string $str
     * @return float
     */
    public static function extractFloat(?string $str = null): float
    {
        if (preg_match('#^(\d+)/(\d+)$#', $str, $matches)) {
            $value = (int)$matches[1] / (float)$matches[2];
        } elseif (preg_match('/35 mm equivalent: (\d+\.\d+) mm/', $str, $matches)) {
            $value = (float)$matches[1];
        } else {
            $value = (float)$str;
        }
        return $value;
    }
}
