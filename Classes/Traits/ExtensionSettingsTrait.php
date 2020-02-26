<?php

namespace Causal\Extractor\Traits;


use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait ExtensionSettingsTrait
{
    /**
     * @var string
     */
    protected $extensionKey = 'extractor';

    /**
     * @var array
     */
    protected $settings;

    protected function getSettings()
    {
        /** @var ExtensionConfiguration $extensionConfiguration */
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        return $extensionConfiguration->get($this->extensionKey);
    }

    protected function initSettings()
    {
        $this->settings = $this->getSettings();
    }
}