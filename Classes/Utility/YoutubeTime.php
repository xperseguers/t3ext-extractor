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
 * Youtube time utility class.
 *
 * @author      Daniel Alexander Damm <dad@imh.dk>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class YoutubeTime
{
    /**
     * Convert Youtube time
     *
     * @param string $youtubeTime
     * @return int
     */
    public static function toSeconds($youtubeTime) : int
    {
        $start = new \DateTime('@0'); // Unix epoch
        $start->add(new \DateInterval($youtubeTime));
        return (int)$start->format('U');
    }
}
