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

namespace Causal\Extractor\Service\Php;

use Causal\Extractor\Service\AbstractService;
use Causal\Extractor\Utility\ColorSpace;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A PhpService service implementation.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PhpService extends AbstractService
{

    /**
     * Returns a list of supported file types.
     *
     * @return array
     */
    public function getSupportedFileTypes()
    {
        return array(
            'gif',      // IMAGETYPE_GIF
            'jpg',      // IMAGETYPE_JPEG
            'jpeg',     // IMAGETYPE_JPEG
            'png',      // IMAGETYPE_PNG
            'bmp',      // IMAGETYPE_BMP
            'tif',      // IMAGETYPE_TIFF_II / IMAGETYPE_TIFF_MM
            'tiff',     // IMAGETYPE_TIFF_II / IMAGETYPE_TIFF_MM
            'wbmp',     // IMAGETYPE_WBMP
            'xbm',      // IMAGETYPE_XBM
            'ico',      // IMAGETYPE_ICO
            'docx',
            'xlsx',
            'pptx',
            'ppsx',
        );
    }

    /**
     * Takes a file reference and extracts its metadata.
     *
     * @param string $fileName Path to the file
     * @return array
     * @see \Causal\ImageAutoresize\Utility\ImageUtility::getMetadata()
     */
    public function extractMetadataFromLocalFile($fileName)
    {
        $extension = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
        switch ($extension) {
            case 'docx':
            case 'xlsx':
            case 'pptx':
            case 'ppsx':
                $metadata = $this->extractMetadataFromOfficeDocument($fileName);
                break;
            default:
                $metadata = $this->extractMetadataFromImage($fileName);
                break;
        }

        return $metadata;
    }

    /**
     * Extracts metadata from a Microsoft Office document.
     *
     * @param string $fileName Path to the file
     * @return array
     */
    protected function extractMetadataFromOfficeDocument($fileName)
    {
        $metadata = array();

        $zip = zip_open($fileName);
        if (is_resource($zip)) {
            while (($zipEntry = zip_read($zip)) !== false) {
                $entryName = zip_entry_name($zipEntry);
                if ($entryName === 'docProps/core.xml' || $entryName === 'docProps/app.xml') {
                    $contents = zip_entry_read($zipEntry, zip_entry_filesize($zipEntry));
                    $data = GeneralUtility::xml2array($contents);
                    $metadata = array_merge_recursive($metadata, $data);
                }
            }
            zip_close($zip);
        }

        return $metadata;
    }

    /**
     * Extracts metadata from an image file.
     *
     * @param string $fileName Path to the file
     * @return array
     */
    protected function extractMetadataFromImage($fileName)
    {
        $metadata = $this->getMetadata($fileName);
        $metadata['Unit'] = 'px';

        // Try to extract additional metadata
        if (($imageSize = getimagesize($fileName)) !== false) {
            $colorSpace = null;
            $accurate = false;
            switch ($imageSize['bits']) {
                case 1:
                    $colorSpace = 'grey';
                    $accurate = true;
                    break;
                case 2:
                    $colorSpace = 'indx';   // More general than 'grey'
                    $accurate = false;
                    break;
                case 8:
                    if ($imageSize['mime'] === 'image/jpeg' && $imageSize['channels'] == 4) {
                        $colorSpace = 'CMYK';
                        $accurate = true;
                    } else {
                        $colorSpace = 'RGB';
                        $accurate = false;  // Could be 'YUV'
                    }
                    break;
            }

            if ($colorSpace !== null) {
                if (!$accurate) {
                    $actualColorSpace = ColorSpace::detect($fileName);
                    if ($actualColorSpace !== null) {
                        $colorSpace = $actualColorSpace;
                    }
                }
                $metadata['ColorSpace'] = $colorSpace;
            }
        }

        return $metadata;
    }

    /**
     * Returns metadata from a given file.
     *
     * @param string $fileName
     * @return array
     * @see \Causal\ImageAutoresize\Utility\ImageUtility::getMetadata()
     */
    protected function getMetadata($fileName)
    {
        $extension = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
        $metadata = array();
        if (GeneralUtility::inList('jpg,jpeg,tif,tiff', $extension) && function_exists('exif_read_data')) {
            $exif = @exif_read_data($fileName);
            if ($exif) {
                $metadata = $exif;
                // Fix description coming from EXIF
                $metadata['ImageDescription'] = static::safeUtf8Encode($metadata['ImageDescription']);

                // Process the longitude/latitude/altitude
                if (isset($metadata['GPSLatitude']) && is_array($metadata['GPSLatitude'])) {
                    $reference = isset($metadata['GPSLatitudeRef']) ? $metadata['GPSLatitudeRef'] : 'N';
                    $decimal = static::rationalToDecimal($metadata['GPSLatitude']);
                    $decimal *= $reference === 'N' ? 1 : -1;
                    $metadata['GPSLatitudeDecimal'] = $decimal;
                }
                if (isset($metadata['GPSLongitude']) && is_array($metadata['GPSLongitude'])) {
                    $reference = isset($metadata['GPSLongitudeRef']) ? $metadata['GPSLongitudeRef'] : 'E';
                    $decimal = static::rationalToDecimal($metadata['GPSLongitude']);
                    $decimal *= $reference === 'E' ? 1 : -1;
                    $metadata['GPSLongitudeDecimal'] = $decimal;
                }
                if (isset($metadata['GPSAltitude'])) {
                    $rationalParts = explode('/', $metadata['GPSAltitude']);
                    $metadata['GPSAltitudeDecimal'] = $rationalParts[0] / $rationalParts[1];
                }
            }
            // Try to extract IPTC data
            $imageinfo = array();
            if (function_exists('iptcparse') && getimagesize($fileName, $imageinfo)) {
                if (isset($imageinfo['APP13'])) {
                    $data = iptcparse($imageinfo['APP13']);
                    $mapping = array(
                        '2#005' => 'Title',
                        '2#025' => 'Keywords',
                        '2#040' => 'Instructions',
                        '2#080' => 'Creator',
                        '2#085' => 'CreatorFunction',
                        '2#090' => 'City',
                        '2#092' => 'Location',
                        '2#095' => 'Region',
                        '2#100' => 'CountryCode',
                        '2#101' => 'Country',
                        '2#103' => 'IdentifierWork',
                        '2#105' => 'CreatorTitle',
                        '2#110' => 'Credit',
                        '2#115' => 'Source',
                        '2#116' => 'Copyright',
                        '2#120' => 'Description',
                        '2#122' => 'DescriptionAuthor',
                    );
                    foreach ($mapping as $iptcKey => $metadataKey) {
                        if (isset($data[$iptcKey])) {
                            $metadata['IPTC' . $metadataKey] = static::safeUtf8Encode($data[$iptcKey][0]);
                        }
                    }
                }
            }
        }
        return $metadata;
    }

    /**
     * Safely converts some text to UTF-8.
     *
     * @param string $text
     * @return string
     * @see \Causal\ImageAutoresize\Utility\ImageUtility::safeUtf8Encode()
     */
    protected static function safeUtf8Encode($text)
    {
        if (function_exists('mb_detect_encoding')) {
            if (mb_detect_encoding($text, 'UTF-8', true) !== 'UTF-8') {
                $text = utf8_encode($text);
            }
        } else {
            // Fall back to hack
            $encoding = mb_detect_encoding($text, 'UTF-8', true);
            $encodedText = utf8_encode($text);
            if (strpos($encodedText, 'Ã') === false) {
                $text = $encodedText;
            }
        }
        return $text;
    }

    /**
     * Converts an EXIF rational into its decimal representation.
     *
     * @param array $components
     * @return float
     * @see \Causal\ImageAutoresize\Utility\ImageUtility::rationaleToDecimal()
     */
    protected static function rationalToDecimal(array $components)
    {
        foreach ($components as $key => $value) {
            $rationalParts = explode('/', $value);
            $components[$key] = $rationalParts[0] / $rationalParts[1];
        }
        list($hours, $minutes, $seconds) = $components;

        return $hours + ($minutes / 60) + ($seconds / 3600);
    }

}
