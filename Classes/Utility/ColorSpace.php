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
     * @param string $str
     * @return string
     */
    public static function normalize($str)
    {
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
        return $str;
    }

    /**
     * Extracts the color space of a given image.
     *
     * @param string $fileName
     * @return string|null
     */
    public static function detect($fileName)
    {
        // Not yet implemented
        return null;
    }

}
