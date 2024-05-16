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

namespace Causal\Extractor\Service\Extraction;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A service to extract metadata from files using API
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ApiMetadataExtraction extends AbstractExtractionService
{
    /**
     * @var integer
     */
    protected $priority = 50;

    /**
     * @var string
     */
    protected $serviceName = 'Api';

    /**
     * ApiMetadataExtraction constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $apiService = $this->getApiService();
        $this->supportedFileExtensions = $apiService->getSupportedFileExtensions();
    }

    /**
     * Checks if the given file can be processed by this extractor.
     *
     * @param File $file
     * @return boolean
     */
    protected function _canProcess(File $file): bool
    {
        $fileExtension = strtolower($file->getProperty('extension'));
        return in_array($fileExtension, $this->supportedFileExtensions);
    }

    /**
     * The actual processing task.
     *
     * Should return an array with database properties for sys_file_metadata to write.
     *
     * @param File $file
     * @param array $previousExtractedData optional, contains the array of already extracted data
     * @return array
     */
    public function extractMetaData(File $file, array $previousExtractedData = [])
    {
        $metadata = [];

        $extractedMetadata = $this->getApiService()->extractMetadata($file);

        error_log(print_r($extractedMetadata, true));
        
        if (!empty($extractedMetadata)) {
            $dataMapping = $this->getDataMapping($file);
            $metadata = $this->remapServiceOutput($extractedMetadata, $dataMapping);
            $this->processCategories($file, $metadata);
        }

        return $metadata;
    }

    /**
     * Returns a API service.
     *
     * @return \Causal\Extractor\Service\Api\ApiService
     */
    protected function getApiService()
    {
        /** @var \Causal\Extractor\Service\Api\ApiService $apiService */
        static $apiService = null;

        if ($apiService === null) {
            $apiService = GeneralUtility::makeInstance(\Causal\Extractor\Service\Api\ApiService::class);
        }

        return $apiService;
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
}
