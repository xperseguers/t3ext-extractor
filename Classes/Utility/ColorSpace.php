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

use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Color space utility class.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ColorSpace
{
    /**
     * Normalizes a color space for use in FAL.
     *
     * @param string|null $str
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function normalize(?string $str = null): ?string
    {
        if (is_array($str)) {
            throw new \InvalidArgumentException('String parameter expected, array given', 1454591450);
        }
        if (MathUtility::canBeInterpretedAsInteger($str)) {
            // See http://www.sno.phy.queensu.ca/~phil/exiftool/TagNames/EXIF.html
            switch ((int)$str) {
                case 1:
                case 2:
                case 0xfffd:
                    $str = 'RGB';
                    break;
                case 3:
                    $str = 'indx';
                    break;
                case 5:
                    $str = 'CMYK';
                    break;
                case 6:
                    $str = 'YUV';
                    break;
            }
        }

        $str = self::verifyAndAdjustWithAllowedColorSpaces($str);

        return $str;
    }

    /**
     * Check against allowed color spaces configured in TCA.
     * Uses a case insensitive comparison and returns the value from TCA if there's a match.
     * Otherwise it returns an empty string.
     *
     * @param string|null $str
     * @return string
     */
    public static function verifyAndAdjustWithAllowedColorSpaces(?string $str = null): string
    {
        if (!isset($GLOBALS['TCA']['sys_file_metadata']['columns']['color_space']['config']['items'])) {
            return $str;
        }

        $allowedColorSpaces = array_map(
            function ($item) {
                return $item[1];
            },
            $GLOBALS['TCA']['sys_file_metadata']['columns']['color_space']['config']['items']
        );

        foreach ($allowedColorSpaces as $allowedColorSpace) {
            if (trim(strtolower($allowedColorSpace)) === trim(strtolower($str))) {
                return $allowedColorSpace;
            }
        }

        return '';
    }

    /**
     * Extracts the color space of a given image.
     *
     * @param string $fileName
     * @return string|null
     */
    public static function detect(string $fileName): ?string
    {
        // Not yet implemented
        return null;
    }
}
