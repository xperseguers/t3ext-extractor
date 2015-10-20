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
 * A service to extract metadata from files using ExifTool.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ExifToolMetadataExtraction extends AbstractExtractionService
{

    /**
     * @var integer
     */
    protected $priority = 80;

    /**
     * ExifToolMetadataExtraction constructor.
     */
    public function __construct()
    {
        /** @var \TYPO3\CMS\Core\Registry $registry */
        $registry = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Registry');
        $this->supportedFileTypes = $registry->get('tx_extractor', 'exiftool.extensions');
        if (empty($this->supportedFileTypes)) {
            if (($exifToolService = $this->getExifToolService()) !== null) {
                $this->supportedFileTypes = $exifToolService->getSupportedFileTypes();
                $registry->set('tx_extractor', 'exiftool.extensions', $this->supportedFileTypes);
            }
        }
    }

    /**
     * Checks if the given file can be processed by this extractor.
     *
     * @param File $file
     * @return boolean
     */
    public function canProcess(File $file)
    {
        $exifToolService = $this->getExifToolService();
        $fileExtension = strtolower($file->getProperty('extension'));
        return ($exifToolService !== null && in_array($fileExtension, $this->supportedFileTypes));
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
    public function extractMetaData(File $file, array $previousExtractedData = array())
    {
        $metadata = array();

        $extractedMetadata = $this->getExifToolService()->extractMetadata($file);
        if (!empty($extractedMetadata)) {
            $dataMapping = $this->getDataMapping('ExifTool', 'metadata');
            $metadata = $this->remapServiceOutput($extractedMetadata, $dataMapping);
        }

        return $metadata;
    }

    /**
     * Returns an ExifTool service.
     *
     * @return \Causal\Extractor\Service\ExifTool\ExifToolService
     */
    protected function getExifToolService()
    {
        /** @var \Causal\Extractor\Service\ExifTool\ExifToolService $exifToolService */
        static $exifToolService = null;

        if ($exifToolService === null) {
            try {
                $exifToolService = GeneralUtility::makeInstance('Causal\\Extractor\\Service\\ExifTool\\ExifToolService');
            } catch (\RuntimeException $e) {
                // Nothing to do
            }
        }

        return $exifToolService;
    }

}
