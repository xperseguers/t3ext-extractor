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
// use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
// use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Resource\File;
use Causal\Extractor\Resource\Event\AfterMetadataExtractedEvent;

/**
 * A ApiService service implementation.
 *
 * @author      Martin Kristensen <mkr@imh.dk> + Daniel Damm <dad@imh.dk>
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

        try {
            switch (true) {
                case $extension === 'youtube':
                    $metadata['youtube'] = $this->extractMetadataFromYoutube($file);
                    break;
                case $extension === 'vimeo':
                    $metadata['vimeo'] = $this->extractMetadataFromVimeo($file);
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
        $metadata = $this->extractMetadataFromLocalFile($file);

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

        $params = array(
            'part' => 'contentDetails,snippet',
            'id' => $this->getMediaId($file),
            'key' => $this->settings['youtube_api_key'],
        );

        $api_url = $this->settings['youtube_api_base'] . '?' . http_build_query($params);
        $result = json_decode(@file_get_contents($api_url), true);

        if(empty($result['items'][0])) {
            return [];
        }
        $metadata = $result['items'][0];

        return $metadata;
    }


    /**
     * Fetch metadata from a Vimeo video.
     *
     * @param \TYPO3\CMS\Core\Resource\File $file File object
     * @return array
     */
    protected function extractMetadataFromVimeo($file)
    {
        static::getLogger()->debug('Fetching metadata from Vimeo API');
        $metadata = [];
        
        // Create a stream - setting headers for authentication
        $opts = array(
            'http'=>array(
            'method'=>"GET",
            'header'=>"cache-control: no-cache\r\n" .
                        "authorization: Bearer ". $this->settings['vimeo_api_key'] ."\r\n"
            )
        );
    
        // Build URI with Params and open the file using the HTTP headers set above
        $api_url = $this->settings['vimeo_api_base'] . $this->getMediaId($file);
        $result = json_decode(@file_get_contents($api_url, false, stream_context_create($opts)), true);

        if(empty($result)) {
            return [];
        }
        $metadata = $result;

        return $metadata;
    }


    /**
     * Fetch online media id from file.
     *
     * @param \TYPO3\CMS\Core\Resource\File $file File object
     * @return string
     */
    protected function getMediaId($file)
    {
                $onlineMediaHelper = OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($file);
                $mediaId = $onlineMediaHelper->getOnlineMediaId($file);

                return $mediaId;
    }
}
