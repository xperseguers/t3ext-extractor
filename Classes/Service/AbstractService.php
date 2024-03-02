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

namespace Causal\Extractor\Service;

use Causal\Extractor\Resource\Event\AfterMetadataExtractedEvent;
use Causal\Extractor\Service\ServiceInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Resource\Event\AfterFileAddedEvent;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Abstract service.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
abstract class AbstractService implements ServiceInterface
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * AbstractService constructor.
     *
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher = null)
    {
        $typo3Branch = class_exists(\TYPO3\CMS\Core\Information\Typo3Version::class)
            ? (new \TYPO3\CMS\Core\Information\Typo3Version())->getBranch()
            : TYPO3_branch;
        if (version_compare($typo3Branch, '9.0', '<')) {
            $this->settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['extractor'] ?? '') ?? [];
        } else {
            $this->settings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('extractor') ?? [];
        }

        if (version_compare($typo3Branch, '10.2', '>=')) {
            $this->eventDispatcher = $eventDispatcher ?? GeneralUtility::getContainer()->get(EventDispatcherInterface::class);
        }
    }

    /**
     * Takes a file reference and extracts its metadata.
     *
     * @param \TYPO3\CMS\Core\Resource\File $file
     * @return array
     */
    public function extractMetadata(File $file)
    {
        static::getLogger()->debug(
            'Extracting metadata',
            [
                'file' => $file->getUid(),
                'identifier' => $file->getCombinedIdentifier(),
            ]
        );
        $localTempFilePath = $file->getForLocalProcessing(false);
        $metadata = $this->extractMetadataFromLocalFile($localTempFilePath);
        $this->cleanupTempFile($localTempFilePath, $file);

        // Emit Signal after metadata has been extracted
        if ($this->eventDispatcher !== null) {
            $event = new AfterMetadataExtractedEvent($file, $metadata);
            $this->eventDispatcher->dispatch($event);
            $metadata = $event->getMetadata();
        } else {
            $this->getSignalSlotDispatcher()->dispatch(
                self::class,
                'postMetaDataExtraction',
                [
                    $file,
                    &$metadata
                ]
            );
        }

        return $metadata;
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

    /**
     * Returns a logger.
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected static function getLogger()
    {
        /** @var \TYPO3\CMS\Core\Log\Logger $logger */
        $logger = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)->getLogger(__CLASS__);
        return $logger;
    }

    /**
     * Returns the SignalSlot dispatcher.
     *
     * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @deprecated since TYPO3 v10
     */
    protected function getSignalSlotDispatcher()
    {
        return GeneralUtility::makeInstance(Dispatcher::class);
    }
}
