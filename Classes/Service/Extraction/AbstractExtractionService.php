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

use Causal\Extractor\Utility\CategoryHelper;
use Causal\Extractor\Utility\ExtensionHelper;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\FolderInterface;
use TYPO3\CMS\Core\Resource\Index\ExtractorInterface;
use TYPO3\CMS\Core\Resource\File;
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
     * @var string
     * @abstract
     */
    protected $serviceName = null;

    /**
     * AbstractService constructor.
     */
    public function __construct()
    {
        $this->settings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('extractor') ?? [];
    }

    /**
     * @var array
     */
    protected $supportedFileExtensions = ['__INVALID__'];

    /**
     * @var array
     */
    protected $supportedFileTypes = [];

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
    public function getFileTypeRestrictions(): array
    {
        return $this->supportedFileTypes;
    }

    /**
     * Returns an array of supported file extensions.
     *
     * @return array
     */
    public function getFileExtensionRestrictions(): array
    {
        return $this->supportedFileExtensions;
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
    public function getDriverRestrictions(): array
    {
        return [
            'Local',
        ];
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
    public function getPriority(): int
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
    public function getExecutionPriority(): int
    {
        return $this->getPriority();
    }

    /**
     * Checks if the given file can be processed by this extractor.
     *
     * @param File $file
     * @return bool
     */
    public function canProcess(File $file): bool
    {
        // We never should/need to process files that have
        // been moved to the recycler folder
        try {
            $parentFolder = $file->getParentFolder();
            do {
                $parent = $parentFolder;
                if ($parent->getRole() === FolderInterface::ROLE_RECYCLER) {
                    return false;
                }
                $parentFolder = $parent->getParentFolder();
            } while ($parent->getIdentifier() !== $parentFolder->getIdentifier());
        } catch (InsufficientFolderAccessPermissionsException $e) {
            // Just go on, we cannot do anything about it
        }

        $metadata = $file->getMetaData();

        if (!empty($metadata) && $metadata['crdate'] !== $metadata['tstamp']) {
            // There's a design flaw in FAL, moving a file should not reindex it
            // because it will effectively lead to overriding any user-defined
            // value by metadata extracted by any extractor again.
            // We will do our small job to refuse to extract metadata if metadata
            // modification timestamp is different from creation timestamp
            // See: https://forge.typo3.org/issues/91168
            return false;
        }

        return $this->_canProcess($file);
    }

    /**
     * Checks if the given file can be processed by this extractor.
     *
     * @param File $file
     * @return bool
     */
    protected abstract function _canProcess(File $file): bool;

    /**
     * Returns the potential mapping files.
     *
     * @param File $file
     * @param array $types
     * @return array
     */
    public function getPotentialMappingFiles(File $file, ?array &$types = null): array
    {
        $potentialFiles = [];
        $types = $this->extensionToServiceTypes($file->getExtension());

        $pathConfiguration = is_dir($this->settings['mapping_base_directory'])
            ? $this->settings['mapping_base_directory']
            : GeneralUtility::getFileAbsFileName($this->settings['mapping_base_directory']);
        if ($pathConfiguration === '' || !is_dir($pathConfiguration)) {
            $pathConfiguration = ExtensionManagementUtility::extPath('extractor') . 'Configuration/Services/';
        }
        $pathConfiguration = rtrim($pathConfiguration, '/') . '/';

        // Always fall back to 'default'...
        $types[] = 'default';

        // ... and even keep compatibility with v1.0 and v1.1
        $types[] = 'metadata';

        foreach ($types as $type) {
            $potentialFiles[] = $pathConfiguration . $this->serviceName . '/' . $type . '.json';
        }

        static::getLogger()->debug(
            'Found potential mapping configuration files',
            [
                'file' => $file->getUid(),
                'identifier' => $file->getCombinedIdentifier(),
                'filenames' => $potentialFiles,
            ]
        );

        return $potentialFiles;
    }

    /**
     * Returns the data mapping for a given service key/subtype.
     *
     * @param File $file
     * @return array|null
     * @throws \Exception
     */
    protected function getDataMapping(File $file): ?array
    {
        $types = [];
        $mappingFiles = $this->getPotentialMappingFiles($file, $types);
        $mappingFileName = null;

        foreach ($mappingFiles as $mappingFile) {
            if (is_file($mappingFile)) {
                $mappingFileName = $mappingFile;
                break;
            }
        }

        static::getLogger()->debug(
            'Mapping configuration file in use',
            [
                'file' => $file->getUid(),
                'identifier' => $file->getCombinedIdentifier(),
                'filename' => $mappingFileName,
            ]
        );

        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extractor']['dataMappingHook'] ?? null)) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extractor']['dataMappingHook'] as $classRef) {
                $hookObject = GeneralUtility::makeInstance($classRef);
                if (!method_exists($hookObject, 'postProcessDataMapping')) {
                    throw new \Exception($classRef . ' must provide a method "postProcessDataMapping', 1425290629);
                }
                $hookData = [
                    'service' => $this->serviceName,
                    'types' => $types,
                    'mappingFileName' => $mappingFileName,
                ];
                $newMappingFileName = $hookObject->postProcessDataMapping($hookData, $this);
                if ($newMappingFileName !== null) {
                    $mappingFileName = $newMappingFileName;
                }
            }
        }

        static::getLogger()->debug(
            'Mapping configuration file in use after hook',
            [
                'filename' => $mappingFileName,
            ]
        );

        // Safeguard
        if (!is_file($mappingFileName)) {
            static::getLogger()->warning('Not considering non-existent mapping configuration file', ['filename' => $mappingFileName]);
            return null;
        }

        $dataMapping = json_decode(file_get_contents($mappingFileName), true);
        if (!is_array($dataMapping)) {
            static::getLogger()->warning('Cannot decode JSON from mapping configuration file', ['filename' => $mappingFileName]);
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
    protected function remapServiceOutput(array $data, ?array $mapping = null): array
    {
        $output = [];

        if (!is_array($mapping)) {
            // Make sure unprocessed arrays will never have a risk to find their way to the database
            foreach ($data as $key => $value) {
                if (!is_array($value)) {
                    $output[$key] = $value;
                }
            }
            return $output;
        }

        $output['__category_uids__'] = $data['__category_uids__'] ?? null;

        foreach ($mapping as $m) {
            $falKey = $m['FAL'];
            $alternativeKeys = $m['DATA'];
            if (!is_array($alternativeKeys)) {
                $alternativeKeys = [$alternativeKeys];
            }

            $value = null;
            foreach ($alternativeKeys as $dataKey) {
                if (strpos($dataKey, '->') !== false) {
                    [$compoundKey, $processor] = explode('->', $dataKey);
                } else {
                    $compoundKey = $dataKey;
                    unset($processor);
                }
                $keys = explode('|', $compoundKey);
                $parentValue = null;
                $value = $data;
                foreach ($keys as $key) {
                    if (substr($key, 0, 7) === 'static:') {
                        $value = substr($key, 7);
                    } else {
                        $parentValue = $value;
                        $value = $value[$key] ?? null;
                    }
                    if ($value === null) {
                        break;
                    }
                }
                if (isset($processor)) {
                    if (preg_match('/^([^(]+)(\((.*)\))?$/', $processor, $matches)) {
                        $processor = $matches[1];
                        $parameters = [$value];
                        if (isset($matches[3])) {
                            if (substr($matches[3], 0, 1) === '\'') {
                                $parameters[] = substr($matches[3], 1, -1);
                            } else {
                                $fields = GeneralUtility::trimExplode(',', $matches[3]);
                                foreach ($fields as $field) {
                                    if (array_key_exists($field, $parentValue)) {
                                        $parameters[] = $parentValue[$field];
                                    }
                                }
                            }
                        }
                        try {
                            $value = call_user_func_array($processor, $parameters);
                        } catch (\Exception $exception) {
                            $value = $parameters[0];
                        } catch (\TypeError $exception) {
                        }
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
            // Known cases: "keywords", "alternative", "SupplementalCategories"
            if (is_array($value)) {
                $output[$key] = implode(', ', $value);
            }
        }

        $this->enforceStringLengths($output);

        static::getLogger()->debug(
            'Raw data remapped to FAL array',
            [
                'input' => $data,
                'output' => $output,
            ]
        );

        return $output;
    }

    /**
     * @param array $output
     */
    protected function enforceStringLengths(array &$output): void
    {
        $typo3Version = (new Typo3Version())->getMajorVersion();
        if ($typo3Version >= 12) {
            $databaseFields = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('sys_file_metadata')
                ->createSchemaManager()
                ->listTableColumns('sys_file_metadata');
        } else {
            $databaseFields = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('sys_file_metadata')
                ->getSchemaManager()
                ->listTableColumns('sys_file_metadata');
        }
        foreach ($output as $key => $value) {
            if (isset($databaseFields[$key])) {
                $type = $databaseFields[$key]->getType();
                if ($typo3Version >= 12) {
                    $typeName = $type->getTypeRegistry()->lookupName($type);
                } else {
                    $typeName = $type->getName();
                }
                $length = $databaseFields[$key]->getLength();
                if ($type === 'string' && strlen($value) > $length) {
                    // We need to truncate the extracted value for the database
                    $output[$key] = substr($value, 0, $length);
                }
            }
        }
    }

    /**
     * Processes the categories.
     *
     * @param File $file
     * @param array $metadata
     */
    protected function processCategories(File $file, array $metadata): void
    {
        CategoryHelper::processCategories($file, $metadata);
    }

    /**
     * Maps a file extension to possible service types.
     *
     * @param string $extension
     * @return string[]
     */
    protected function extensionToServiceTypes(string $extension): array
    {
        // Normalize the extension
        $extension = strtolower($extension);
        switch ($extension) {
            case 'aif':
                $extension = 'aiff';
                break;
            case 'htm':
            case 'phtml':
                $extension = 'html';
                break;
            case 'jpeg':
                $extension = 'jpg';
                break;
            case 'midi':
                $extension = 'mid';
                break;
            case 'mpeg':
                $extension = 'mpg';
                break;
            case 'tiff':
                $extension = 'tif';
                break;
        }

        $types = [];

        // Most-specific service type first
        $types[] = $extension;

        // Then category of file
        $types[] = ExtensionHelper::getExtensionCategory($extension);

        return $types;
    }

    /**
     * Returns a logger.
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    abstract protected static function getLogger();
}
