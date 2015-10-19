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

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Abstract Tika service.
 *
 * @author      Ingo Renner <ingo@typo3.org>
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
abstract class AbstractTikaService implements TikaServiceInterface
{

    /**
     * @var array
     */
    protected $settings;

    /**
     * AbstractTikaService constructor.
     */
    public function __construct()
    {
        $this->settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['extractor']);
    }

    /**
     * Removes a temporary file.
     *
     * When working with a file, the actual file might be on a remote storage.
     * To work with it it gets copied to local storage, those temporary local
     * copies need to be removed when they're not needed anymore.
     *
     * @param string $localTempFilePath Path to the local file copy
     * @param \TYPO3\CMS\Core\Resource\File $sourceFile Original file
     * @return void
     */
    protected function cleanupTempFile($localTempFilePath, File $sourceFile)
    {
        if (PathUtility::basename($localTempFilePath) !== $sourceFile->getName()) {
            unlink($localTempFilePath);
        }
    }

}
