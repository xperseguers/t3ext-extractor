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
 * Number utility class.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_extractor
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Number {

	/**
	 * Extracts an integer at the end of a string.
	 *
	 * @param string $str
	 * @return string
	 */
	public function extractIntegerAtEnd($str) {
		if (preg_match('/(\d+)$/', $str, $matches)) {
			return (int)$matches[1];
		}
		return null;
	}

}
