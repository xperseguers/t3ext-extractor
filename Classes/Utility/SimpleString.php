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
     * @param string $str
     * @return string
     */
    public static function trim($str): string
    {
        // Remove non-printable characters (ASCII 0-31)
        $str = preg_replace('/[\x00-\x1F]/', '', $str);

        return trim($str) ?? '';
    }
}
