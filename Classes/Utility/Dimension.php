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
 * Dimension utility class.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Dimension
{
    /**
     * Extracts the width from a dimension string for use in FAL.
     *
     * @param string|null $str
     * @return int|null
     */
    public static function extractWidth(?string $str = null): ?int
    {
        $width = null;
        if (preg_match('/^(\d+.?\d*) x (\d+.?\d*) (\S+)/', $str, $matches)) {
            if ($matches[3] === 'pts') {
                $width = (int)round(static::pointsToMillimeters((float)$matches[1]));
            } else {
                $width = (int)round($matches[1]);
            }
        }
        return $width;
    }

    /**
     * Extracts the height from a dimension string for use in FAL.
     *
     * @param string|null $str
     * @return int|null
     */
    public static function extractHeight(?string $str = null): ?int
    {
        $height = null;
        if (preg_match('/^(\d+.?\d*) x (\d+.?\d*) (\S+)/', $str, $matches)) {
            if ($matches[3] === 'pts') {
                $height = (int)round(static::pointsToMillimeters((float)$matches[2]));
            } else {
                $height = (int)round($matches[2]);
            }
        }
        return $height;
    }

    /**
     * Extracts the dimension unit from a dimension string for use in FAL.
     *
     * @param string|null $str
     * @return string|null
     */
    public static function extractUnit(?string $str = null): ?string
    {
        $unit = null;
        if (preg_match('/^(\d+.?\d*) x (\d+.?\d*) (\S+)/', $str, $matches)) {
            $unit = $matches[3];
            switch ($unit) {
                case 'pts':
                    // We already converted to mm
                    $unit = 'mm';
                    break;
            }
        }
        return $unit;
    }

    /**
     * Converts points into millimeters.
     *
     * @param float $value
     * @return float
     */
    protected static function pointsToMillimeters(float $value): float
    {
        // 1 inch = 72 points
        $inches = $value / 72;
        // 1 inch = 2.54 cm
        $millimeters = $inches * 25.4;
        return $millimeters;
    }
}
