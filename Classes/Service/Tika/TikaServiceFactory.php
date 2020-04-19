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

namespace Causal\Extractor\Service\Tika;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Provides a Tika service based on configuration.
 *
 * @author      Ingo Renner <ingo@typo3.org>
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class TikaServiceFactory
{
    /**
     * Creates an instance of a Tika service
     *
     * @param string $tikaService Tika Service type, one of jar or server
     * @return TikaServiceInterface
     * @throws \InvalidArgumentException for unknown Tika service type
     * @throws \RuntimeException if the service cannot be instantiated
     */
    public static function getTika($tikaService = '')
    {
        if (empty($tikaService)) {
            if (version_compare(TYPO3_version, '9.0', '<')) {
                $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['extractor'] ?? '') ?? [];
                $tikaService = $settings['tika_mode'];
            } else {
                $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
                $tikaService = $extensionConfiguration->get('extractor', 'tika_mode');
            }
        }

        switch ($tikaService) {
            case 'jar':
                return GeneralUtility::makeInstance(\Causal\Extractor\Service\Tika\AppService::class);
            case 'server':
                return GeneralUtility::makeInstance(\Causal\Extractor\Service\Tika\ServerService::class);
            default:
                throw new \InvalidArgumentException(
                    'Unknown Tika service type "' . $tikaService . '". Must be one of "jar" or "server".',
                    1445096066
                );
        }
    }
}
