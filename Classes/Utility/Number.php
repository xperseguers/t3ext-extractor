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
    public static function castInteger($str): int
    {
        return (int)$str;
    }

    /**
     * Extracts an integer at the end of a string.
     *
     * @param string $str
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function extractIntegerAtEnd($str): string
    {
        if (is_array($str)) {
            throw new \InvalidArgumentException('String parameter expected, array given', 1454591285);
        }
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
     * @throws \InvalidArgumentException
     */
    public static function extractFloat($str): float
    {
        if (is_array($str)) {
            throw new \InvalidArgumentException('String parameter expected, array given', 1454591360);
        }
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
