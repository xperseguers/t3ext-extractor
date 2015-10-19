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
 * GPS utility class.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_extractor
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Gps {

	/**
	 * Converts a latitude/longitude from a string representation to
	 * its decimal value.
	 *
	 * @param string $str DDD MM.MMMM
	 * @param string $reference ('N', 'S', 'E', 'W')
	 * @return string DDD.DDDD
	 */
	public static function toDecimal($str, $reference = '') {
		$decimal = null;
		if ($reference !== '') {
			$str = $reference . ' ' . $str;
		}

		if (preg_match('/^([NSEW]) (\d+)° (\d+.\d+)\'?$/', $str, $matches)) {
			$decimal = (int)$matches[2];
			$minutes = floor($matches[3]);
			$seconds = 60 * (float)($matches[3] - $minutes);
			$decimal += $minutes / 60;
			$decimal += $seconds / 3600;

			$reference = $matches[1];
			$decimal *= $reference === 'N' || $reference === 'E' ? 1 : -1;

		} elseif (preg_match('/^([NSEW]) (\d+\.\d+)° (\d+\.\d+)\' (\d+\.\d+)"$/', $str, $matches)) {
			$decimal = (float)$matches[2];
			$minutes = (float)$matches[3];
			$seconds = (float)$matches[4];
			$decimal += $minutes / 60;
			$decimal += $seconds / 3600;

			$reference = $matches[1];
			$decimal *= $reference === 'N' || $reference === 'E' ? 1 : -1;
		}

		return $decimal;
	}

}
