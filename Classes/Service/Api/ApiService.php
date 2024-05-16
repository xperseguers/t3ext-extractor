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

namespace Causal\Extractor\Service\Api;

use Causal\Extractor\Service\AbstractService;
use Causal\Extractor\Utility\ColorSpace;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Resource\File;

use Causal\Extractor\Resource\Event\AfterMetadataExtractedEvent;

/**
 * A ApiService service implementation.
 *
 * @author      --- <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ApiService extends AbstractService
{
    /** @var array */
    protected $onlineExtensions = [
        'youtube',  // VIDEOTYPE_YOUTUBE
        'vimeo',    // VIDEOTYPE_VIMEO
    ];


    

    /**
     * Returns a list of supported file types.
     *
     * @return array
     */
    public function getSupportedFileExtensions()
    {
        return array_merge(
            $this->onlineExtensions
        );
    }

    /**
     * Takes a file reference and extracts its metadata.
     *
     * @param \TYPO3\CMS\Core\Resource\File $file Path to the file
     * @return array
     * @see \Causal\ImageAutoresize\Utility\ImageUtility::getMetadata()
     */
    public function extractMetadataFromLocalFile($file)
    {
        $fileName = $file->getForLocalProcessing(false);
        $extension = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
        
        //ok
        error_log("extension navn:");
        
        error_log($extension);

        try {
            switch (true) {
                
                // case in_array($extension, $this->getOnlineExtensions):
                //     $metadata = $this->extractMetadataWithGetId3($fileName);
                //     break;
                case $extension === 'youtube':
                    $metadata = $this->extractMetadataFromYoutube($file);
                    break;
                case $extension === 'vimeo':
                    $metadata = $this->extractMetadataFromVimeo($file);
                    break;
                default:
                    $metadata = [];
                    break;
            }
            $this->cleanupTempFile($fileName, $file);

            static::getLogger()->debug('Metadata extracted', $metadata);
        } catch (\Exception $e) {
            static::getLogger()->error('Error while extracting metadata from file', ['exception' => $e]);
            $metadata = [];
        }
        
        
        error_log("metadate første tomme array:");
        error_log(print_r($metadata, true));
        return $metadata;
    }

    


    /**
     * Takes a file reference and extracts its metadata.
     *
     * @param File $file
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
        #$localTempFilePath = $file->getForLocalProcessing(false);
        $metadata = $this->extractMetadataFromLocalFile($file);
        #$this->cleanupTempFile($localTempFilePath, $file);

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
     * Fetch metadata from a youtube video.
     *
     * @param \TYPO3\CMS\Core\Resource\File $file File object
     * @return array
     */
    protected function extractMetadataFromYoutube($file)
    {
        static::getLogger()->debug('Fetching metadata from Youtube API');
        $metadata = [];

        // Fetch online media id
        $onlineMediaHelper = OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($file);
        $videoId = $onlineMediaHelper->getOnlineMediaId($file);
        
        $params = array(
            'part' => 'contentDetails,snippet', // snippet has description
            'id' => $videoId,
            'key' => $this->settings['youtube_api_key'],
        );

        $api_url = $this->settings['youtube_api_base'] . '?' . http_build_query($params);
        
        $result = json_decode(@file_get_contents($api_url), true);

        if(empty($result['items'][0]['contentDetails'])) {
            return null;
        }
        
        $duration = self::covTime($result['items'][0]['contentDetails']['duration']);
        $metadata['duration'] = $duration;

        return $metadata;

    }


    /**
     * Convert Youtube time
     *
     * @param string $youtubeTime
     */
    public function covTime($youtubeTime)
    {
        $start = new \DateTime('@0'); // Unix epoch
        $start->add(new \DateInterval($youtubeTime));
        return $start->format('U');
    }

}

