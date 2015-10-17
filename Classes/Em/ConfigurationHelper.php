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

use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class providing configuration checks for EXT:extractor.
 *
 * @category    Em
 * @package     TYPO3
 * @subpackage  tx_extractor
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ConfigurationHelper
{

    /**
     * @var array
     */
    protected $settings;

    /**
     * ConfigurationHelper constructor.
     */
    public function __construct()
    {
        $this->settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['extractor']);
    }

    /**
     * Creates an input field for the Tika Jar path and checks its validity.
     *
     * @param array $params Field information to be rendered
     * @param \TYPO3\CMS\Extensionmanager\ViewHelpers\Form\TypoScriptConstantsViewHelper $pObj : The calling parent object.
     * @return string
     */
    public function createTikaJarPath(array $params, $pObj)
    {
        $html = $this->createFormInputField(array(
            'name' => $params['fieldName'],
            'id' => 'em-' . $params['propertyName'],
            'class' => 'form-control',
            'placeholder' => '/path/to/tika-app-x.x.jar',
            'value' => $params['fieldValue'],
        ));

        $info = array();
        $tikaService = $this->getTikaService();
        $javaRuntimeInfo = $tikaService->getJavaRuntimeInfo();
        if (!empty($javaRuntimeInfo)) {
            $info['Java'] = $javaRuntimeInfo['path'];
            if (!empty($javaRuntimeInfo['description'])) {
                $info['Java Runtime'] = $javaRuntimeInfo['description'];
            } else {
                $info['Java Runtime'] = $javaRuntimeInfo['version'];
            }
            if (!empty($params['fieldValue'])) {
                $tikaVersion = $tikaService->getTikaVersion();
                $info['Tika'] = $tikaVersion ?: 'n/a';
            }
        }
        $html .= $this->createInfoBlock($info);

        return $html;
    }

    /**
     * Creates an input field for the Tika server host and checks its validity.
     *
     * @param array $params Field information to be rendered
     * @param \TYPO3\CMS\Extensionmanager\ViewHelpers\Form\TypoScriptConstantsViewHelper $pObj : The calling parent object.
     * @return string
     */
    public function createTikaServerHost(array $params, $pObj)
    {
        $html = $this->createFormInputField(array(
            'name' => $params['fieldName'],
            'id' => 'em-' . $params['propertyName'],
            'class' => 'form-control',
            'placeholder' => 'localhost',
            'value' => $params['fieldValue'],
        ));

        // TODO: check configuration

        return $html;
    }

    /**
     * Creates an input field for an external tool and checks its validity.
     *
     * @param array $params Field information to be rendered
     * @param \TYPO3\CMS\Extensionmanager\ViewHelpers\Form\TypoScriptConstantsViewHelper $pObj : The calling parent object.
     * @return string
     */
    public function createToolInput(array $params, $pObj)
    {
        $externalTool = explode('_', $params['propertyName']);
        $externalTool = $externalTool[1];

        if (empty($params['fieldValue'])) {
            // Try to find the external tool on our own
            $cmd = CommandUtility::getCommand($externalTool);
            if ($cmd !== false) {
                $params['fieldValue'] = $cmd;
            }
        }

        $html = $this->createFormInputField(array(
            'name' => $params['fieldName'],
            'id' => 'em-' . $params['propertyName'],
            'class' => 'form-control',
            'placeholder' => '/path/to/' . $externalTool,
            'value' => $params['fieldValue'],
        ));

        if (!empty($params['fieldValue']) && file_exists($params['fieldValue'])) {
            $cmd = $params['fieldValue'];
            switch ($externalTool) {
                case 'exiv2':
                    $cmd .= ' -V';
                    break;
                case 'pdfinfo':
                    $cmd .= ' -v 2>&1';
                    break;
            }

            $output = array();
            CommandUtility::exec($cmd, $output);
            if (!empty($output)) {
                $info = array('Version' => $output[0]);
                $html .= $this->createInfoBlock($info);
            }
        }

        return $html;
    }

    /**
     * Creates a HTML form input field.
     *
     * @param array $attributes
     * @return string
     */
    protected function createFormInputField(array $attributes)
    {
        $html = '<input type="text"';
        foreach ($attributes as $key => $value) {
            $html .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        $html .= ' />';
        return $html;
    }

    /**
     * Creates an information block.
     *
     * @param array $info
     * @return string
     */
    protected function createInfoBlock(array $info)
    {
        $html = '';
        if (!empty($info)) {
            $html .= '<ul style="margin-top:1em;">';
            foreach ($info as $key => $value) {
                $html .= '<li>' . htmlspecialchars($key) . ': <strong>' . htmlspecialchars($value) . '</strong></li>';
            }
            $html .= '</ul>';
        }
        return $html;
    }

    /**
     * Translates a label.
     *
     * @param string $id
     * @param array $arguments
     * @return string
     */
    protected function translate($id, array $arguments = null)
    {
        $value = $this->getLanguageService()->sL('LLL:EXT:ig_ldap_sso_auth/Resources/Private/Language/locallang_db.xlf:' . $id);
        $value = empty($value) ? $id : $value;

        if (is_array($arguments) && $value !== null) {
            return vsprintf($value, $arguments);
        } else {
            return $value;
        }
    }

    /**
     * Returns the Apache Tika service.
     *
     * @return \Causal\Extractor\Service\Tika
     */
    protected function getTikaService()
    {
        /** @var \Causal\Extractor\Service\Tika $tikaService */
        $tikaService = GeneralUtility::makeInstance('Causal\\Extractor\\Service\\Tika', $this->settings);
        return $tikaService;
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
