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
     * @var array The message severity class names
     */
    protected static $classes = [
        FlashMessage::NOTICE => 'notice',
        FlashMessage::INFO => 'info',
        FlashMessage::OK => 'success',
        FlashMessage::WARNING => 'warning',
        FlashMessage::ERROR => 'danger'
    ];

    /**
     * @var array The message severity icon names
     */
    protected static $icons = [
        FlashMessage::NOTICE => 'lightbulb-o',
        FlashMessage::INFO => 'info',
        FlashMessage::OK => 'check',
        FlashMessage::WARNING => 'exclamation',
        FlashMessage::ERROR => 'times'
    ];

    /**
     * Creates an input field for the Tika Jar path and checks its validity.
     *
     * @param array $params Field information to be rendered
     * @param \TYPO3\CMS\Install\ViewHelpers\Form\TypoScriptConstantsViewHelper $pObj
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
        $html = $this->createFormInputField(array(
            'name' => $params['fieldName'],
            'id' => 'em-' . $params['propertyName'],
            'class' => 'form-control',
            'placeholder' => 'localhost',
            'value' => $params['fieldValue'],
        ));

        if (!empty($params['fieldValue'])) {
            $info = array();
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
                case 'exiftool':
                    $cmd .= ' -ver';
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
     * Creates a checkbox.
     *
     * @param array $params
     * @param \TYPO3\CMS\Install\ViewHelpers\Form\TypoScriptConstantsViewHelper $pObj
     * @return string
     */
    public function createCheckboxForExtensionManager(array $params, $pObj)
    {
        list($title, $message) = GeneralUtility::trimExplode(':', $this->translate('settings.disabled'), true, 2);
        /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
        $flashMessage = GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Messaging\FlashMessage::class,
            $message,
            $title,
            FlashMessage::INFO
        );

        $title = '';
        if (!empty($flashMessage->getTitle())) {
            $title = '<h4 class="alert-title">' . $flashMessage->getTitle() . '</h4>';
        }
        $html = '
			<div class="alert ' . $this->getClass($flashMessage) . '">
				<div class="media">
					<div class="media-left">
						<span class="fa-stack fa-lg">
							<i class="fa fa-circle fa-stack-2x"></i>
							<i class="fa fa-' . $this->getIconName($flashMessage) . ' fa-stack-1x"></i>
						</span>
					</div>
					<div class="media-body">
						' . $title . '
						<div class="alert-message">' . $flashMessage->getMessage() . '</div>
					</div>
				</div>
			</div>';

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

    /**
     * Gets the message severity class name
     *
     * @param FlashMessage $flashMessage
     *
     * @return string The message severity class name
     */
    protected function getClass(FlashMessage $flashMessage): string
    {
        return 'alert-' . self::$classes[$flashMessage->getSeverity()];
    }

    /**
     * Gets the message severity icon name
     *
     * @param FlashMessage $flashMessage
     *
     * @return string The message severity icon name
     */
    protected function getIconName(FlashMessage $flashMessage): string
    {
        return self::$icons[$flashMessage->getSeverity()];
    }
}
