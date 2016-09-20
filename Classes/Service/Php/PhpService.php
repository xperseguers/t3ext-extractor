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
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * A PhpService service implementation.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PhpService extends AbstractService
{

    /** @var array */
    protected $imageExtensions = array(
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
    );

    /** @var array */
    protected $officeDocumentExtensions = array(
        'docx',
        'xlsx',
        'pptx',
        'ppsx',
    );

    /** @var array */
    protected $getId3Extensions = array(
        '3gp',
        'aa',
        'aac',
        'ac3',
        'amr',
        'au',
        'avr',
        'dss',
        'dts',
        'flac',
        'la',
        'lpac',
        'm4a',
        'mid',
        'midi',
        'mp3',
        'mp4',
        'mpeg',
        'mpg',
        'ogg',
        'voc',
        'wav',
        'wma',
        'wmv',
    );

    /**
     * Returns a list of supported file types.
     *
     * @return array
     */
    public function getSupportedFileExtensions()
    {
        return array_merge(
            $this->imageExtensions,
            $this->officeDocumentExtensions,
            $this->getId3Extensions,
            array('pdf')
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
        switch (true) {
            case in_array($extension, $this->officeDocumentExtensions):
                $metadata = $this->extractMetadataFromOfficeDocument($fileName);
                break;
            case in_array($extension, $this->getId3Extensions):
                $metadata = $this->extractMetadataWithGetId3($fileName);
                break;
            case $extension === 'pdf':
                $metadata = $this->extractMetadataFromPdf($fileName);
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
     * Extracts metadata using the GetID3 library.
     *
     * @param string $fileName Path to the file
     * @return array
     */
    protected function extractMetadataWithGetId3($fileName)
    {
        $extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('extractor');
        require_once($extensionPath . 'Resources/Private/vendor/getID3/getid3/getid3.php');

        $getID3 = new \getID3();
        $getID3->setOption(array('encoding' => 'UTF-8'));

        $metadata = $getID3->analyze($fileName);
        \getid3_lib::ksort_recursive($metadata);

        return $metadata;
    }

    /**
     * Extracts metadata from a PDF document.
     *
     * @param string $fileName Path to the file
     * @return array
     */
    protected function extractMetadataFromPdf($fileName)
    {
        $metadata = array();

        $fh = fopen($fileName, 'r');
        if (is_resource($fh)) {
            $xmpPointer = 0;
            $objects = array();
            while (($buffer = fgets($fh, 1024000)) !== false) {
                if (preg_match('/^(\d+) \d+ obj(.*)/', $buffer, $matches)) {
                    $object = trim($matches[2]);
                    while (($b = fgets($fh, 1024000)) !== false) {
                        if (trim($b) === 'endobj') {
                            break;
                        }
                        $object .= $b;
                    }
                    // TODO: check if $object is worth keeping (to lower memory usage)
                    $objects[$matches[1]] = $object;
                    if (strpos($object, '<x:xmpmeta ') !== false) {
                        $xmpPointer = $matches[1];
                    }
                }
            }
            fclose($fh);

            // Native PDF metadata are referenced in position 1
            if (isset($objects[1])) {
                if (preg_match_all('#/([A-Za-z]+) (\d+) 0 R#', $objects[1], $matches)) {
                    foreach ($matches[1] as $index => $key) {
                        $referencedObject = $matches[2][$index];
                        $value = trim($objects[$referencedObject]);
                        if (preg_match('/^\((.+)\)$/', $value, $m)) {
                            $metadata[$key] = $m[1];
                        }
                    }
                }
            }
            if (isset($objects[3])) {
                if (preg_match('#<< /Type /Pages .* /Count (\d+)#', $objects[3], $matches)) {
                    $metadata['Pages'] = (int)$matches[1];
                }
            }

            // XMP block
            if ($xmpPointer > 0) {
                $contents = $objects[$xmpPointer];
                $start = strpos($contents, '<x:xmpmeta ');
                $end = strpos($contents, '</x:xmpmeta>');
                $contents = substr($contents, $start, $end - $start + 12);
                // Remove namespaces
                $contents = preg_replace('#(</?)([a-z]+):([A-Za-z]+)#', '\1\3', $contents);
                $xml = simplexml_load_string($contents);
                $data = @json_decode(@json_encode($xml), true);
                foreach ($data['RDF']['Description'] as $index => $values) {
                    if (!MathUtility::canBeInterpretedAsInteger($index)) {
                        $values = array(
                            $index => $values,
                        );
                    }
                    foreach ($values as $key => $value) {
                        if (isset($value['Seq'])) {
                            $value = implode(', ', $value['Seq']);
                        } elseif (isset($value['Alt'])) {
                            $value = implode(', ', $value['Alt']);
                        } elseif (isset($value['Bag'])) {
                            $value = implode(', ', $value['Bag']);
                        } elseif (is_array($value)) {
                            $value = implode(', ', $value);
                        }
                        if (!empty($value)) {
                            $metadata['xmp:' . $key] = $value;
                        }
                    }
                }
            }
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
                    if (!empty($rationalParts[1])) {
                        $metadata['GPSAltitudeDecimal'] = $rationalParts[0] / $rationalParts[1];
                    } else {
                        $metadata['GPSAltitudeDecimal'] = 0;
                    }
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
            if (!empty($rationalParts[1])) {
                $components[$key] = $rationalParts[0] / $rationalParts[1];
            } else {
                $components[$key] = 0;
            }
        }
        list($hours, $minutes, $seconds) = $components;

        return $hours + ($minutes / 60) + ($seconds / 3600);
    }

}
