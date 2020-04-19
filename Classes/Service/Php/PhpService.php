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
    protected $imageExtensions = [
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
    ];

    /** @var array */
    protected $officeDocumentExtensions = [
        'docx',
        'xlsx',
        'pptx',
        'ppsx',
    ];

    /** @var array */
    protected $getId3Extensions = [
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
    ];

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
            ['pdf']
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

        static::getLogger()->debug('Metadata extracted', $metadata);
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
        static::getLogger()->debug('Extracting metadata from MS Office document');

        $metadata = [];

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
        static::getLogger()->debug('Extracting metadata with GetID3 library');

        // Require 3rd-party libraries, in case TYPO3 does not run in composer mode
        $pharFileName = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('extractor') . 'Libraries/james-heinrich-getid3.phar';
        if (is_file($pharFileName)) {
            @include 'phar://' . $pharFileName . '/vendor/autoload.php';
        }

        $getID3 = new \getID3();
        $getID3->setOption(['encoding' => 'UTF-8']);

        $metadata = $getID3->analyze($fileName);
        \getid3_lib::ksort_recursive($metadata);

        return $metadata;
    }

    /**
     * @param string $buffer
     * @param int $bufferSize
     * @param int $startPosition
     * @param array $metaDataLength
     */
    private function extractXMPMetaRecursive($buffer, $bufferSize, $startPosition, &$metaDataLength)
    {
        // 'XML/Type/Metadata' in hex
        $startSequence = "\x58\x4D\x4C\x2F\x54\x79\x70\x65\x2F\x4D\x65\x74\x61\x64\x61\x74\x61";
        $nextStartPosition = strpos($buffer, $startSequence, $startPosition);
        // seek 30 bytes back to find the Length Number "/Length *****/Subtype/XML/Type/Metadata"
        $lengthHeaderOffset = 30;

        if ($nextStartPosition !== false && $nextStartPosition - $lengthHeaderOffset > 0) {
            $headerData = substr($buffer, $nextStartPosition - $lengthHeaderOffset, $lengthHeaderOffset);
            $matches = [];
            preg_match_all('/<<\/Length\ (\d+)\/Subtype\//m', $headerData, $matches, PREG_SET_ORDER, 0);
            if (!empty($matches) && isset($matches[0][1])) {
                $metaLen = (int)$matches[0][1];
                if ($metaLen > 0) {
                    // skip $startSequence and stream "XML/Type/Metadata>>stream\r\n"
                    $metaDataStartOffset = $nextStartPosition + 27;
                    $metaDataLength[] = [
                        'offset' => $metaDataStartOffset,
                        'length' => $metaLen
                    ];
                    // jump over the meta data section and start looking for another metadata section
                    $nextPossibleStartPosition = $metaDataStartOffset + $metaLen;
                    if ($nextPossibleStartPosition < $bufferSize) {
                        $this->extractXMPMetaRecursive($buffer, $bufferSize, $nextPossibleStartPosition, $metaDataLength);
                    }
                }
            }
        }
    }

    /**
     * @param \DOMNodeList $domNodeList
     * @param array $xmpMetadata
     * @param string $parentTitle
     */
    private function parseRDFXMPDataRecursive($domNodeList, &$xmpMetadata, $parentTitle = '')
    {
        /** @var \DOMNode $childNode */
        foreach ($domNodeList as $childNode) {
            if ($childNode->nodeType === XML_ELEMENT_NODE || $childNode instanceof \DOMElement) {
                $subNodes = $childNode->childNodes;
                // single TEXT NODE
                if ($subNodes->length === 1 && $subNodes->item(0)->nodeType === XML_TEXT_NODE) {

                    $value = trim($subNodes->item(0)->nodeValue);
                    if (MathUtility::canBeInterpretedAsInteger($value)) {
                        $value = (int)$value;
                    } elseif (MathUtility::canBeInterpretedAsFloat($value)) {
                        $value = (float)$value;
                    }
                    $tagName = $parentTitle . '_' . $childNode->tagName;
                    if (!isset($xmpMetadata[$tagName])) {
                        $xmpMetadata[$tagName] = $value;
                    } else {
                        if (!is_array($xmpMetadata[$tagName])) {
                            $scalarValue = $xmpMetadata[$tagName];
                            $xmpMetadata[$tagName] = [$scalarValue];
                        }
                        $xmpMetadata[$tagName][] = $value;
                    }
                } elseif ($subNodes->length > 0) { // go deeper
                    $this->parseRDFXMPDataRecursive($subNodes, $xmpMetadata, $parentTitle . '_' . $childNode->tagName);
                }
            }
        }
    }

    /**
     * @param string $xmpXMLData
     * @return array
     */
    private function parseXMPMetaXML($xmpXMLData)
    {
        // we don't support AES encrypted METADATA currently
        if (strpos($xmpXMLData, '<?xpacket') === false) {
            return [];
        }

        $xmpMetadata = [];
        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->loadXML($xmpXMLData);
        $dom->encoding = 'UTF-8';
        $dom->preserveWhiteSpace = false;
        $dom->substituteEntities = false;

        if ('x:xmpmeta' !== $dom->documentElement->nodeName) {
            return $xmpMetadata;
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
        $baseNode = 'rdf:Description';
        $nodes = $xpath->query('//' . $baseNode);

        if ($nodes->length > 0) {
            $this->parseRDFXMPDataRecursive($nodes, $xmpMetadata);
        }

        $sanitizedMetadata = [];
        $baseKeySequenceToRemove = '_' . $baseNode . '_';
        $staticReplaceKeyMap = [
            'dc:creator_rdf:Seq_rdf:li' => 'xmp:creator',
            'dc:title_rdf:Alt_rdf:li' => 'xmp:title',
            'dc:description_rdf:Alt_rdf:li' => 'xmp:description',
            'dc:rights_rdf:Alt_rdf:li' => 'xmp:right',
            'dc:subject_rdf:Bag_rdf:li' => 'xmp:subject',
            'pdf:Keywords' => 'xmp:Keywords',
            'pdf:Producer' => 'xmp:Producer',
        ];

        foreach ($xmpMetadata as $key => $value) {
            $sanitizedKey = str_replace($baseKeySequenceToRemove, '', $key);
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            if (isset($staticReplaceKeyMap[$sanitizedKey])) {
                $sanitizedKey = $staticReplaceKeyMap[$sanitizedKey];
            } else {
                $subKeyList = explode(':', $sanitizedKey);
                $lastKeyIndex = count($subKeyList) - 1;
                if ($lastKeyIndex >= 0) {
                    $sanitizedKey = 'xmp:' . $subKeyList[$lastKeyIndex];
                }
            }

            $sanitizedMetadata[$sanitizedKey] = $value;
        }

        return $sanitizedMetadata;
    }

    /**
     * @param string $buffer
     * @return array
     */
    private function extractNativePDFInformation($buffer)
    {
        $nativeData = [];
        // find pageNumber
        $re = '/<< \/Type \/Pages (.*) \/Count (\d+)/m';
        $matches = [];
        preg_match_all($re, $buffer, $matches, PREG_SET_ORDER, 0);
        if (!empty($matches) && isset($matches[0][2])) {
            $nativeData['Pages'] = (int)$matches[0][2];
        }

        $matches = [];
        // TODO: braces in value can cause a early exit in regex for this group
        $nativeMetaData = '/\/([a-zA-Z]+)\((.+?)[\)\/]/m';
        preg_match_all($nativeMetaData, $buffer, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $match) {
            if (!empty($match[1]) && !empty($match[2])) {
                $key = $match[1];
                $value = $match[2];
                $nativeData[trim($key)] = trim($value);
            }
        }

        return $nativeData;
    }

    /**
     * Extracts metadata from a PDF document.
     *
     * @param string $fileName Path to the file
     * @return array
     */
    protected function extractMetadataFromPdf($fileName)
    {
        static::getLogger()->debug('Extracting metadata from PDF');
        $metadata = [];

        $fh = fopen($fileName, 'rb');
        if (is_resource($fh)) {
            clearstatcache();
            $chunkSize = 1002400;
            // rollback of the complete "<</Length *****/Subtype/XML/Type/Metadata>>stream "to
            // length is dynamic but 50 should be a good catch all
            $staticOffsetRollback = 50;
            $metaDataLength = [];

            // find all XMP data block offsets
            while (!feof($fh)) {
                $currentPointerPosition = ftell($fh);
                $buffer = fread($fh, $chunkSize);
                if ($buffer !== false) {
                    $metaDataChunkLength = [];
                    $this->extractXMPMetaRecursive($buffer, strlen($buffer), 0, $metaDataChunkLength);
                    if (!empty($metaDataChunkLength)) {
                        $metaDataLength[] = [
                            'file_offset' => $currentPointerPosition,
                            'locations' => $metaDataChunkLength
                        ];
                    }

                    $nativeData = $this->extractNativePDFInformation($buffer);
                    foreach ($nativeData as $key => $value) {
                        $metadata[$key] = $value;
                    }

                    unset($buffer); // clear memory from scope
                    if (!feof($fh)) {
                        fseek($fh, -$staticOffsetRollback, SEEK_CUR);
                    }
                } else {
                    break;
                }
            }

            // load and parse all found XMP Data Blocks
            $metadataItems = [];
            foreach ($metaDataLength as $possibleMetadataLocation) {
                $fileOffset = $possibleMetadataLocation['file_offset'];
                foreach ($possibleMetadataLocation['locations'] as $subMetadataLocation) {
                    $chunkOffset = $subMetadataLocation['offset'];
                    $metaContentLength = $subMetadataLocation['length'];
                    $absoluteFileLocation = $fileOffset + $chunkOffset;
                    fseek($fh, $absoluteFileLocation, SEEK_SET);
                    $metadataContent = fread($fh, $metaContentLength);
                    $metadataItem = $this->parseXMPMetaXML(trim($metadataContent));
                    if (!empty($metadataItem) && count($metadataItem) > 1) {
                        $metadataItems[] = $metadataItem;
                    }
                }
            }
            fclose($fh);

            // currently we only want the last found meta data
            $metaDataBlockCount = count($metadataItems);
            if ($metaDataBlockCount > 0 && !empty($metadataItems[$metaDataBlockCount - 1])) {
                $item = $metadataItems[$metaDataBlockCount - 1];
                foreach ($item as $key => $value) {
                    $metadata[$key] = $value;
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
        static::getLogger()->debug('Extracting metadata from image');

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
        $metadata = [];

        if (!file_exists($fileName)) {
            // Early exit if the file to be analysed is in fact "missing" locally
            return $metadata;
        }

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
            $imageinfo = [];
            if (function_exists('iptcparse') && getimagesize($fileName, $imageinfo)) {
                if (isset($imageinfo['APP13'])) {
                    $data = iptcparse($imageinfo['APP13']);
                    $mapping = [
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
                    ];
                    foreach ($mapping as $iptcKey => $metadataKey) {
                        if (isset($data[$iptcKey])) {
                            if ($metadataKey === 'Keywords') {
                                foreach ($data[$iptcKey] as $keyword) {
                                    $metadata['IPTC' . $metadataKey][] = static::safeUtf8Encode($keyword);
                                }
                            } else {
                                $metadata['IPTC' . $metadataKey] = static::safeUtf8Encode($data[$iptcKey][0]);
                            }
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
     * @param string|null $text
     * @return string|null
     * @see \Causal\ImageAutoresize\Utility\ImageUtility::safeUtf8Encode()
     */
    protected static function safeUtf8Encode(?string $text = null): ?string
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

