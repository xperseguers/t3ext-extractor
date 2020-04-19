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

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Causal\Extractor\Service\Tika\TikaServiceFactory;

/**
 * Class providing configuration checks for EXT:extractor.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ConfigurationHelper extends AbstractConfigurationField
{

    /**
     * Creates an input field for the Tika Jar path and checks its validity.
     *
     * @param array $params Field information to be rendered
     * @param \TYPO3\CMS\Install\ViewHelpers\Form\TypoScriptConstantsViewHelper $pObj
     * @return string
     */
    public function createTikaJarPath(array $params, $pObj)
    {
        $html = $this->createFormInputField([
            'name' => $params['fieldName'],
            'id' => 'em-' . $params['propertyName'],
            'class' => 'form-control',
            'placeholder' => '/path/to/tika-app-x.x.jar',
            'value' => $params['fieldValue'],
        ]);

        $info = [];

        try {
            /** @var \Causal\Extractor\Service\Tika\AppService $tikaService */
            $tikaService = TikaServiceFactory::getTika('jar');
            $javaRuntimeInfo = $tikaService->getJavaRuntimeInfo();

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
        } catch (\RuntimeException $e) {
            // Nothing to do
        }

        $html .= $this->createInfoBlock($info);

        return $html;
    }

    /**
     * Creates an input field for the Tika server host and checks its validity.
     *
     * @param array $params Field information to be rendered
     * @param \TYPO3\CMS\Install\ViewHelpers\Form\TypoScriptConstantsViewHelper $pObj
     * @return string
     */
    public function createTikaServerHost(array $params, $pObj)
    {
        $html = $this->createFormInputField([
            'name' => $params['fieldName'],
            'id' => 'em-' . $params['propertyName'],
            'class' => 'form-control',
            'placeholder' => 'localhost',
            'value' => $params['fieldValue'],
        ]);

        if (!empty($params['fieldValue'])) {
            $info = [];
            $success = false;

            try {
                /** @var \Causal\Extractor\Service\Tika\ServerService $tikaService */
                $tikaService = TikaServiceFactory::getTika('server');

                $ping = $tikaService->ping();
                if ($ping !== -1) {
                    $success = true;
                    $info['Ping'] = $ping . ' ms';
                    $tikaVersion = $tikaService->getTikaVersion();
                    $info['Tika'] = $tikaVersion ?: 'n/a';
                }

                $html .= $this->createInfoBlock($info);
            } catch (\RuntimeException $e) {
                // Nothing to do
            }

            $html .= '<div style="margin-top:1em">';
            $html .= '<img src="../../typo3conf/ext/extractor/Documentation/Images/' . (
                    $success ? 'animation-ok.gif' : 'animation-ko.gif'
                ) . '" alt="" />';
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Creates an input field for an external tool and checks its validity.
     *
     * @param array $params Field information to be rendered
     * @param \TYPO3\CMS\Install\ViewHelpers\Form\TypoScriptConstantsViewHelper $pObj
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

        $html = $this->createFormInputField([
            'name' => $params['fieldName'],
            'id' => 'em-' . $params['propertyName'],
            'class' => 'form-control',
            'placeholder' => '/path/to/' . $externalTool,
            'value' => $params['fieldValue'],
        ]);

        if (!empty($params['fieldValue']) && file_exists($params['fieldValue'])) {
            $cmd = $params['fieldValue'];
            switch ($externalTool) {
                case 'exiftool':
                    $cmd .= ' -ver';
                    break;
                case 'pdfinfo':
                    $cmd .= ' -v 2>&1';
                    break;
            }

            $output = [];
            CommandUtility::exec($cmd, $output);
            if (!empty($output)) {
                $info = ['Version' => $output[0]];
                $html .= $this->createInfoBlock($info);
            }
        }

        return $html;
    }

    /**
     * Creates a HTML form input field.
     *
     * @param array $attributes
     * @param string $type
     * @return string
     */
    protected function createFormInputField(array $attributes, $type = 'text')
    {
        $html = '<input type="' . $type . '"';
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
}
