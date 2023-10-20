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

namespace Causal\Extractor\Em;

use Causal\Extractor\Utility\SimpleString;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract class for configuration fields.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
abstract class AbstractConfigurationField
{
    /**
     * Translates a label.
     *
     * @param string $id
     * @param bool $hsc
     * @param array $arguments
     * @return string
     */
    protected function translate($id, $hsc = false, array $arguments = null)
    {
        if (!SimpleString::isFirstPartOfStr($id, 'LLL:EXT:')) {
            $reference = 'LLL:EXT:extractor/Resources/Private/Language/locallang_em.xlf:' . $id;
        } else {
            $reference = $id;
        }
        $value = $this->getLanguageService()->sL($reference);
        $value = empty($value) ? $id : $value;

        if (is_array($arguments) && $value !== null) {
            $value = vsprintf($value, $arguments);
        }

        return $hsc ? htmlspecialchars($value) : $value;
    }

    /**
     * Returns the LanguageService.
     *
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
