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
 * Karaoke utility class.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Karaoke
{
    /**
     * Extracts karaoke data.
     *
     * @param array $data
     * @return string
     */
    public static function extract(array $data): ?string
    {
        if ($data[0] !== '@KMIDI KARAOKE FILE') {
            return null;
        }

        // Skip metadata
        $i = 1;
        while ($data[$i]{0} === '@') {
            $i++;
        }

        $buffer = '';
        $length = count($data);
        for (; $i < $length; $i++) {
            $chunk = utf8_encode($data[$i]);
            if (strpos($chunk, '/') === 0) {
                $chunk{0} = PHP_EOL;
            } elseif ($chunk{0} === '\\') {
                $chunk = PHP_EOL . PHP_EOL . substr($chunk, 1);
            }
            $buffer .= $chunk;
        }

        return trim($buffer);
    }
}
