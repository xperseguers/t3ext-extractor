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
 * Flash utility class.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Flash
{
    /**
     * Normalize an EXIF flash value to its integer representation
     *
     * This is needed because some manufacturers store the description
     * of how the flash behaved instead of the integer value as given
     * in the specifications.
     *
     * @param string|null $str
     * @return int|null
     */
    public static function normalize(?string $str = null, ?string $reference = null): ?int
    {
        if ($str === null) {
            return null;
        }

        if (MathUtility::canBeInterpretedAsInteger($str)) {
            return (int)$str;
        }

        // See https://www.exiftool.org/TagNames/EXIF.html#Flash
        $mapping = [
            0x0 => 'No Flash',
            0x1 => 'Fired',
            0x5 => 'Fired, Return not detected',
            0x7 => 'Fired, Return detected',
            0x8 => 'On, Did not fire',
            0x9 => 'On, Fired',
            0xd => 'On, Return not detected',
            0xf => 'On, Return detected',
            0x10 => 'Off, Did not fire',
            0x14 => 'Off, Did not fire, Return not detected',
            0x18 => 'Auto, Did not fire',
            0x19 => 'Auto, Fired',
            0x1d => 'Auto, Fired, Return not detected',
            0x1f => 'Auto, Fired, Return detected',
            0x20 => 'No flash function',
            0x30 => 'Off, No flash function',
            0x41 => 'Fired, Red-eye reduction',
            0x45 => 'Fired, Red-eye reduction, Return not detected',
            0x47 => 'Fired, Red-eye reduction, Return detected',
            0x49 => 'On, Red-eye reduction',
            0x4d => 'On, Red-eye reduction, Return not detected',
            0x4f => 'On, Red-eye reduction, Return detected',
            0x50 => 'Off, Red-eye reduction',
            0x58 => 'Auto, Did not fire, Red-eye reduction',
            0x59 => 'Auto, Fired, Red-eye reduction',
            0x5d => 'Auto, Fired, Red-eye reduction, Return not detected',
            0x5f => 'Auto, Fired, Red-eye reduction, Return detected',
        ];

        $key = array_search($str, $mapping);

        return $key !== false ? $key : 0;
    }
}
