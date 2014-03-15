<?php
namespace Causal\Extractor\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Xavier Perseguers <xavier@causal.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

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
	 * @return string DDD.DDDD
	 */
	static public function toDecimal($str) {
		$decimal = NULL;

		if (preg_match('/^([NSEW]) (\d+)° (\d+.\d+)\'?$/', $str, $matches)) {
			$decimal = (int)$matches[2];
			$minutes = floor($matches[3]);
			$seconds = 60 * (float)($matches[3] - $minutes);
			$decimal += $minutes / 60;
			$decimal += $seconds / 3600;

			$reference = $matches[1];
			$decimal *= $reference === 'N' || $reference === 'E' ? 1 : -1;
		}

		return $decimal;
	}

}
