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
 * A service to extract metadata from files using exiftool.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PdfinfoMetadataExtraction extends AbstractExtractionService
{

    /**
     * @var array
     */
    protected $supportedFileTypes = array('pdf');

    /**
     * @var integer
     */
    protected $priority = 60;

    /**
     * Checks if the given file can be processed by this extractor.
     *
     * @param File $file
     * @return boolean
     */
    public function canProcess(File $file)
    {
        $pdfinfoService = $this->getPdfinfoService();
        $fileExtension = strtolower($file->getProperty('extension'));
        return ($pdfinfoService !== null && in_array($fileExtension, $this->supportedFileTypes));
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

        $extractedMetadata = $this->getPdfinfoService()->extractMetadata($file);
        if (!empty($extractedMetadata)) {
            $dataMapping = $this->getDataMapping('Pdfinfo', 'metadata');
            $metadata = $this->remapServiceOutput($extractedMetadata, $dataMapping);
        }

        return $metadata;
    }

    /**
     * Returns an ExifTool service.
     *
     * @return \Causal\Extractor\Service\ExifTool\PdfinfoService
     */
    protected function getPdfinfoService()
    {
        /** @var \Causal\Extractor\Service\ExifTool\PdfinfoService $pdfinfoService */
        static $pdfinfoService = null;

        if ($pdfinfoService === null) {
            try {
                $pdfinfoService = GeneralUtility::makeInstance('Causal\\Extractor\\Service\\Pdfinfo\\PdfinfoService');
            } catch (\RuntimeException $e) {
                // Nothing to do
            }
        }

        return $pdfinfoService;
    }

}
