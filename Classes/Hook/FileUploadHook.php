<?php
namespace Causal\Extractor\Hook;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * This class allows metadata to be extracted automatically after
 * uploading a file.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class FileUploadHook implements \TYPO3\CMS\Core\Utility\File\ExtendedFileUtilityProcessDataHookInterface
{
    /**
     * @var \TYPO3\CMS\Core\Resource\Index\ExtractorInterface[]
     */
    protected static $extractionServices = null;

    /**
     * @param string $action The action
     * @param array $cmdArr The parameter sent to the action handler
     * @param array $result The results of all calls to the action handler
     * @param \TYPO3\CMS\Core\Utility\File\ExtendedFileUtility $pObj The parent object
     * @return void
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function processData_postProcessAction(
        $action,
        array $cmdArr,
        array $result,
        \TYPO3\CMS\Core\Utility\File\ExtendedFileUtility $pObj
    ) {
        if ($action === 'upload') {
            /** @var \TYPO3\CMS\Core\Resource\File[] $fileObjects */
            $fileObjects = array_pop($result);
            if (!is_array($fileObjects)) {
                return;
            }

            foreach ($fileObjects as $fileObject) {
                static::getLogger()->debug(
                    'File uploaded',
                    [
                        'file' => $fileObject->getUid(),
                        'identifier' => $fileObject->getCombinedIdentifier(),
                    ]
                );
                $storageRecord = $fileObject->getStorage()->getStorageRecord();
                if ($storageRecord['driver'] === 'Local') {
                    $this->runMetaDataExtraction($fileObject);
                    // Emit Signal after meta data has been extracted
                    $this->getSignalSlotDispatcher()->dispatch(
                        self::class,
                        'postMetaDataExtraction',
                        [$storageRecord]
                    );
                }
            }
        }
    }

    /**
     * Runs the metadata extraction for a given file.
     *
     * @param \TYPO3\CMS\Core\Resource\File $fileObject
     * @return void
     * @see \TYPO3\CMS\Core\Resource\Index\Indexer::runMetaDataExtraction
     */
    protected function runMetaDataExtraction(\TYPO3\CMS\Core\Resource\File $fileObject)
    {
        if (static::$extractionServices === null) {
            $extractorRegistry = \TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::getInstance();
            static::$extractionServices = $extractorRegistry->getExtractorsWithDriverSupport('Local');
        }

        $newMetaData = [
            0 => $fileObject->_getMetaData(),
        ];
        foreach (static::$extractionServices as $service) {
            static::getLogger()->debug(
                'Checking availability of extraction service',
                [
                    'service' => get_class($service),
                ]
            );
            if ($service->canProcess($fileObject)) {
                $metaData = $service->extractMetaData($fileObject, $newMetaData);
                $newMetaData[$service->getPriority()] = $metaData;
                static::getLogger()->debug(
                    'Metadata extracted',
                    [
                        'service' => get_class($service),
                        'metadata' => $metaData,
                    ]
                );
            }
        }

        // Sorting and overloading metadata by priority
        ksort($newMetaData);
        $metaData = [];
        foreach ($newMetaData as $data) {
            $metaData = array_merge($metaData, $data);
        }

        static::getLogger()->debug(
            'Updating metadata for file',
            [
                'file' => $fileObject->getUid(),
                'identifier' => $fileObject->getCombinedIdentifier(),
                'metadata' => $metaData,
            ]
        );
        $fileObject->_updateMetaDataProperties($metaData);
        $metaDataRepository = \TYPO3\CMS\Core\Resource\Index\MetaDataRepository::getInstance();
        $metaDataRepository->update($fileObject->getUid(), $metaData);
    }

    /**
     * Returns a logger.
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected static function getLogger(): \TYPO3\CMS\Core\Log\Logger
    {
        /** @var \TYPO3\CMS\Core\Log\Logger $logger */
        static $logger = null;
        if ($logger === null) {
            $logger = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)->getLogger(__CLASS__);
        }

        return $logger;
    }

    /**
     * Returns the SignalSlot dispatcher.
     *
     * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected function getSignalSlotDispatcher(): Dispatcher
    {
        return GeneralUtility::makeInstance(Dispatcher::class);
    }
}
