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

use TYPO3\CMS\Core\Resource\Index\ExtractorInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractExtractionService
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
abstract class AbstractExtractionService implements ExtractorInterface
{

    /**
     * @var array
     */
    protected $settings;

    /**
     * AbstractService constructor.
     */
    public function __construct()
    {
        $this->settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['extractor']);
    }

    /**
     * @var array
     */
    protected $supportedFileTypes = array('__INVALID__');

    /**
     * Priority in handling extraction.
     *
     * @var int
     */
    protected $priority = 1;

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
     * Returns all supported DriverTypes.
     *
     * Since some processors may only work for local files, and other
     * are especially made for processing files from remote.
     *
     * Returns array of strings with driver names of Drivers which are supported,
     * If the driver did not register a name, it's the class name.
     * empty array indicates no restrictions.
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
     *
     * Defines the precedence if several processors
     * can handle the same file.
     *
     * Should be between 1 and 100, 100 is more important than 1.
     *
     * @return integer
     */
    public function getPriority()
    {
        return max(1, min(100, $this->priority));
    }

    /**
     * Returns the execution priority of the extraction Service.
     *
     * Should be between 1 and 100, 100 means runs as first service, 1 runs at last service.
     *
     * @return integer
     */
    public function getExecutionPriority()
    {
        return $this->getPriority();
    }

    /**
     * Returns the data mapping for a given service key/subtype.
     *
     * @param string $service
     * @param string $type
     * @return array|null
     * @throws \Exception
     */
    protected function getDataMapping($service, $type = '')
    {
        $pathConfiguration = GeneralUtility::getFileAbsFileName($this->settings['mapping_base_directory'], false);
        if ($pathConfiguration === '') {
            $pathConfiguration = ExtensionManagementUtility::extPath('extractor') . 'Configuration/Services/';
        }
        $pathConfiguration = rtrim($pathConfiguration, '/') . '/';

        if (empty($type) || $type === '*') {
            $type = 'default';
        }
        $mappingFileName = $pathConfiguration . $service . '/' . $type . '.json';
        if (!is_file($mappingFileName) && $type !== 'default') {
            // Try a default mapping
            $mappingFileName = $pathConfiguration . $service . '/default.json';
        }

        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extractor']['dataMappingHook'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extractor']['dataMappingHook'] as $classRef) {
                $hookObject = GeneralUtility::getUserObj($classRef);
                if (!method_exists($hookObject, 'postProcessDataMapping')) {
                    throw new \Exception($classRef . ' must provide a method "postProcessDataMapping', 1425290629);
                }
                $hookData = array(
                    'service' => $service,
                    'type' => $type,
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
    protected function remapServiceOutput(array $data, array $mapping = null)
    {
        $output = array();

        if (!is_array($mapping)) {
            // Make sure unprocessed arrays will never have a risk to find their way to the database
            foreach ($data as $key => $value) {
                if (!is_array($value)) {
                    $output[$key] = $value;
                }
            }
            return $output;
        }

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

        foreach ($output as $key => $value) {
            // Known cases: "keywords", "alternative"
            if (is_array($value)) {
                $output[$key] = implode(', ', $value);
            }
        }

        return $output;
    }

}
