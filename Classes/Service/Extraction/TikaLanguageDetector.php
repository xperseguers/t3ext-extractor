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

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\File;

/**
 * A service to detect language from files using Apache Tika.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class TikaLanguageDetector extends AbstractExtractionService
{

    /**
     * @var array
     */
    protected $supportedFileTypes = array(
        'doc','docx','epub','htm','html','msg','odf','odt','pdf','ppt','pptx',
        'rtf','sxw','txt','xls','xlsx'
    );

    /**
     * @var integer
     */
    protected $priority = 98;

    /**
     * @var bool
     */
    protected $tikaAvailable;

    /**
     * Returns an array of supported file types.
     *
     * An empty array indicates all file types.
     *
     * @return array
     */
    public function getFileTypeRestrictions()
    {
        return $this->supportedFileTypes;
    }

    /**
     * Checks if the given file can be processed by this extractor.
     *
     * @param File $file
     * @return boolean
     */
    public function canProcess(File $file)
    {
        $tikaService = $this->getTikaService();
        $fileExtension = strtolower($file->getProperty('extension'));
        return ($tikaService !== null && in_array($fileExtension, $this->supportedFileTypes));
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
        $metadata['language'] = $this->getTikaService()->detectLanguage($file);

        return $metadata;
    }

    /**
     * Returns a Tika service.
     *
     * @return \Causal\Extractor\Service\Tika\TikaServiceInterface
     */
    protected function getTikaService()
    {
        static $tikaService = null;
        if ($tikaService === null) {
            try {
                $tikaService = \Causal\Extractor\Service\Tika\TikaServiceFactory::getTika();
            } catch (\RuntimeException $e) {
                // Nothing to do
            }
        }
        return $tikaService;
    }

}
