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

namespace Causal\Extractor\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource;

/**
 * This is a bridge service between TYPO3 metadata extraction services
 * and FAL extractors for Local Driver.
 *
 * @category    Service
 * @package     TYPO3
 * @subpackage  tx_extractor
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Bridge implements \TYPO3\CMS\Core\Resource\Index\ExtractorInterface
{

    /**
     * @var array
     */
    protected static $serviceSubTypes = array();

    /**
     * @var string
     */
    protected $extKey = 'extractor';

    /**
     * @var bool
     */
    protected $debug;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
        $this->debug = (isset($configuration['debug']) && (bool)$configuration['debug']);
    }

    /**
     * Returns an array of supported file types
     * An empty array indicates all file types
     *
     * @return array
     */
    public function getFileTypeRestrictions()
    {
        return array();
    }

    /**
     * Gets all supported DriverTypes.
     *
     * Since some processors may only work for local files, and other
     * are especially made for processing files from remote.
     *
     * Returns array of strings with driver names of Drivers which are supported,
     * If the driver did not register a name, it's the class name.
     * empty array indicates no restrictions
     *
     * @return array
     */
    public function getDriverRestrictions()
    {
        return array(
            'Local',
        );
    }

    /**
     * Returns the data priority of the processing Service.
     * Defines the precedence if several processors
     * can handle the same file.
     *
     * Should be between 1 and 100, 100 is more important than 1
     *
     * @return integer
     */
    public function getPriority()
    {
        return 50;
    }

    /**
     * Returns the execution priority of the extraction Service.
     * Should be between 1 and 100, 100 means runs as first service, 1 runs at last service.
     *
     * @return integer
     */
    public function getExecutionPriority()
    {
        return 50;
    }

    /**
     * Checks if the given file can be processed by this Extractor
     *
     * @param Resource\File $file
     * @return boolean
     */
    public function canProcess(Resource\File $file)
    {
        $serviceSubTypes = $this->findServiceSubTypesByExtension($file->getProperty('extension'));
        return count($serviceSubTypes) > 0;
    }

    /**
     * The actual processing TASK.
     *
     * Should return an array with database properties for sys_file_metadata to write
     *
     * @param Resource\File $file
     * @param array $previousExtractedData optional, contains the array of already extracted data
     * @return array
     */
    public function extractMetaData(Resource\File $file, array $previousExtractedData = array())
    {
        $serviceSubTypes = $this->findServiceSubTypesByExtension($file->getProperty('extension'));

        $metadata = array();
        foreach ($serviceSubTypes as $serviceSubType) {
            $data = $this->getMetadata($file, $serviceSubType);

            // Existing data has precedence over new information, due to service's precedence
            $metadata = array_merge($data, $metadata);
        }

        // Extract language
        $languageMetatada = $this->getLanguage($file);
        if (!empty($languageMetatada)) {
            // Existing data has precedence over new information, due to service's precedence
            $metadata = array_merge($languageMetatada, $metadata);
        }

        return $metadata;
    }

    /**
     * Returns an array of available serviceSubType keys providing metadata extraction
     * for a given file extension.
     *
     * @param string $extension
     * @return array
     */
    protected function findServiceSubTypesByExtension($extension)
    {
        if (isset(static::$serviceSubTypes[$extension])) {
            return static::$serviceSubTypes[$extension];
        }

        $serviceSubTypes = array();
        $service = GeneralUtility::makeInstanceService('metaExtract', $extension);
        if (is_object($service)) {
            $serviceSubTypes[] = $extension;
        }
        unset($service);

        if (GeneralUtility::inList('jpg,jpeg,tif,tiff', $extension)) {
            $alternativeServiceSubTypes = array('image:iptc', 'image:exif');
            foreach ($alternativeServiceSubTypes as $alternativeServiceSubType) {
                $service = GeneralUtility::makeInstanceService('metaExtract', $alternativeServiceSubType);
                if (is_object($service)) {
                    $serviceSubTypes[] = $alternativeServiceSubType;
                }
                unset($service);
            }
        }

        static::$serviceSubTypes[$extension] = $serviceSubTypes;
        return $serviceSubTypes;
    }

    /**
     * Returns metadata for a given file.
     *
     * @param Resource\File $file
     * @param array $serviceSubType
     * @return array
     */
    protected function getMetadata(Resource\File $file, $serviceSubType)
    {
        $data = array();
        $serviceChain = '';

        /** @var \TYPO3\CMS\Core\Service\AbstractService $serviceObj */
        while (is_object($serviceObj = GeneralUtility::makeInstanceService('metaExtract', $serviceSubType, $serviceChain))) {
            $serviceChain .= ',' . $serviceObj->getServiceKey();

            $dataMapping = $this->getDataMapping($serviceObj->getServiceKey(), $serviceSubType);
            if (empty($dataMapping)) {
                continue;
            }

            $fileName = $file->getForLocalProcessing(false);
            $serviceObj->setInputFile($fileName, $file->getProperty('extension'));

            if ($serviceObj->process()) {
                $output = $serviceObj->getOutput();
                if (is_array($output)) {
                    if ($this->debug) {
                        $this->debugServiceOutput($serviceObj->getServiceKey(), $serviceSubType, $fileName, $output);
                    }
                    $output = $this->remapServiceOutput($output, $dataMapping);

                    // Existing data has precedence over new information, due to service's precedence
                    $data = array_merge($output, $data);
                }
            }
        }

        return $data;
    }

    /**
     * Returns language metadata of a given file.
     *
     * @param Resource\File $file
     * @return array
     */
    protected function getLanguage(Resource\File $file)
    {
        $data = array();
        $serviceChain = '';

        /** @var \TYPO3\CMS\Core\Service\AbstractService $serviceObj */
        while (is_object($serviceObj = GeneralUtility::makeInstanceService('textLang', '*', $serviceChain))) {
            $serviceChain .= ',' . $serviceObj->getServiceKey();

            $dataMapping = $this->getDataMapping($serviceObj->getServiceKey());
            if (empty($dataMapping)) {
                continue;
            }

            $fileName = $file->getForLocalProcessing(false);
            $serviceObj->setInputFile($fileName, $file->getProperty('extension'));

            if ($serviceObj->process()) {
                $output = $serviceObj->getOutput();
                if (!empty($output)) {
                    $output = explode(LF, $output);
                    if ($this->debug) {
                        $this->debugServiceOutput($serviceObj->getServiceKey(), $serviceSubType, $fileName, $output);
                    }
                    $data = $this->remapServiceOutput($output, $dataMapping);
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Returns the data mapping for a given service key/subtype.
     *
     * @param string $serviceKey
     * @param string $serviceSubType
     * @return array|null
     * @throws \Exception
     */
    protected function getDataMapping($serviceKey, $serviceSubType = '')
    {
        $pathConfiguration = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($this->extKey) . 'Configuration/Services/';

        if (empty($serviceSubType) || $serviceSubType === '*') {
            $serviceSubType = 'default';
        }
        $mappingFileName = $pathConfiguration . $serviceKey . '/' . str_replace(':', '_', $serviceSubType) . '.json';
        if (!is_file($mappingFileName) && $serviceSubType !== 'default') {
            // Try a default mapping
            $mappingFileName = $pathConfiguration . $serviceKey . '/default.json';
        }

        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extractor']['dataMappingHook'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extractor']['dataMappingHook'] as $classRef) {
                $hookObject = GeneralUtility::getUserObj($classRef);
                if (!method_exists($hookObject, 'postProcessDataMapping')) {
                    throw new \Exception($classRef . ' must provide a method "postProcessDataMapping', 1425290629);
                }
                $hookData = array(
                    'serviceKey' => $serviceKey,
                    'serviceSubType' => $serviceSubType,
                    'mappingFileName' => $mappingFileName,
                );
                $newMappingFileName = $hookObject->postProcessDataMapping($hookData, $this);
                if ($newMappingFileName !== null) {
                    $mappingFileName = $newMappingFileName;
                }
            }
        }

        // Safeguard
        if (!is_file($mappingFileName)) {
            return null;
        }

        $dataMapping = json_decode(file_get_contents($mappingFileName), true);
        if (!is_array($dataMapping)) {
            return null;
        }

        return $dataMapping;
    }

    /**
     * Remaps $data coming from a service to a FAL-compliant array.
     *
     * @param array $data
     * @param array $mapping
     * @return array
     */
    protected function remapServiceOutput(array $data, array $mapping)
    {
        $output = array();

        foreach ($mapping as $m) {
            $falKey = $m['FAL'];
            $alternativeKeys = $m['DATA'];
            if (!is_array($alternativeKeys)) {
                $alternativeKeys = array($alternativeKeys);
            }

            $value = null;
            foreach ($alternativeKeys as $dataKey) {
                list($compoundKey, $processor) = explode('->', $dataKey);
                $keys = explode('|', $compoundKey);
                $parentValue = null;
                $value = $data;
                foreach ($keys as $key) {
                    if (substr($key, 0, 7) === 'static:') {
                        $value = substr($key, 7);
                    } else {
                        $parentValue = $value;
                        $value = isset($value[$key]) ? $value[$key] : null;
                    }
                    if ($value === null) {
                        break;
                    }
                }
                if (isset($processor)) {
                    if (preg_match('/^([^(]+)(\((.*)\))?$/', $processor, $matches)) {
                        $processor = $matches[1];
                        $parameters = array($value);
                        if (isset($matches[3])) {
                            $fields = GeneralUtility::trimExplode(',', $matches[3]);
                            foreach ($fields as $field) {
                                $parameters[] = $parentValue[$field];
                            }
                        }
                        $value = call_user_func_array($processor, $parameters);
                    }
                }
                if ($value !== null) {
                    // Do not try any further alternative key, we have a value
                    break;
                }
            }

            if ($value !== null && $value !== '') {
                $output[$falKey] = $value;
            }
        }

        return $output;
    }

    /**
     * Debugs the output of a given service.
     *
     * @param string $serviceKey
     * @param string $serviceSubType
     * @param string $fileName
     * @param array $output
     * @return void
     */
    protected function debugServiceOutput($serviceKey, $serviceSubType, $fileName, array $output)
    {
        $logPath = GeneralUtility::getFileAbsFileName('typo3temp/tx_extractor/');
        GeneralUtility::mkdir_deep($logPath);
        $logFilename = date('Ymd-His-') . GeneralUtility::shortMD5($fileName) . '-' . GeneralUtility::shortMD5($serviceKey . $serviceSubType) . '.log';

        $content = array();
        $content[] = 'File:    ' . $fileName;
        $content[] = 'Service: ' . $serviceKey;
        $content[] = 'Subtype: ' . $serviceSubType;
        $content[] = 'Output:';
        $content[] = var_export($output, true);

        @file_put_contents($logPath . $logFilename, implode(LF, $content));
    }

}
