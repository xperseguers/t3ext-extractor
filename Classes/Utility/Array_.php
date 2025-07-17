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
 * Array utility class.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Array_
{
    /**
     * Concatenates items from an array.
     *
     * @param array $pieces
     * @param string $glue
     * @return string|null
     */
    public static function concatenate($pieces, string $glue): ?string
    {
        if (!is_array($pieces)) {
            return null;
        }

        return implode($glue, $pieces);
    }
}
