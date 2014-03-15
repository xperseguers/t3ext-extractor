<?php
namespace Causal\Extractor\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Xavier Perseguers <xavier@causal.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

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
class Bridge implements \TYPO3\CMS\Core\Resource\Index\ExtractorInterface {

	/**
	 * @var array
	 */
	static protected $serviceSubTypes = array();

	/**
	 * Returns an array of supported file types
	 * An empty array indicates all file types
	 *
	 * @return array
	 */
	public function getFileTypeRestrictions() {
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
	public function getDriverRestrictions() {
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
	public function getPriority() {
		return 50;
	}

	/**
	 * Returns the execution priority of the extraction Service.
	 * Should be between 1 and 100, 100 means runs as first service, 1 runs at last service.
	 *
	 * @return integer
	 */
	public function getExecutionPriority() {
		return 50;
	}

	/**
	 * Checks if the given file can be processed by this Extractor
	 *
	 * @param Resource\File $file
	 * @return boolean
	 */
	public function canProcess(Resource\File $file) {
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
	public function extractMetaData(Resource\File $file, array $previousExtractedData = array()) {
		$serviceSubTypes = $this->findServiceSubTypesByExtension($file->getProperty('extension'));

		$falMapping = array(
			'caption' => '',
			'color_space' => 'color_space,EXIF_ColorSpaceInformation',
			'content_creation_date' => 'date_cr|1,date_cr|0',
			'content_modification_date' => 'date_mod',
			'creator' => 'EXIF_CameraSoftware,creator',
			'creator_tool' => 'file_creator|0,EXIF_CameraModel',
			'description' => 'IPTC_caption,description',
			'duration' => '',
			'height' => 'EXIF_ImageHeight',
			'keywords' => 'IPTC_keywords,keywords',
			'language' => '',
			'latitude' => 'EXIF_Latitude->gpsToDecimal',
			'location_city' => 'IPTC_city,loc_city',
			'location_country' => 'IPTC_country,IPTC_country_code,loc_country',
			'location_region' => 'IPTC_state',
			'longitude' => 'EXIF_Longitude->gpsToDecimal',
			'note' => '',
			'pages' => '',
			'publisher' => 'EXIF_Photographer,publisher',
			'ranking' => '',
			'source' => 'IPTC_source',
			'status' => '',
			'title' => 'IPTC_headline,title',
			'unit' => '',
			'width' => 'EXIF_ImageWidth',
		);

		$metadata = array();
		foreach ($serviceSubTypes as $serviceSubType) {
			$data = $this->getMetadata($file, $serviceSubType);
			foreach ($falMapping as $key => $mapping) {
				$dataMapping = explode(',', $mapping);
				if (empty($metadata[$key])) {
					foreach ($dataMapping as $dataKey) {
						// TODO: handle subkeys and post-processing (GPS to decimal)
						if (!empty($data[$dataKey])) {
							$metadata[$key] = $data[$dataKey];
						}
					}
				}
			}
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
	protected function findServiceSubTypesByExtension($extension) {
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
			$alternativeServiceSubType = array('image:exif', 'image:iptc');
			foreach ($alternativeServiceSubType as $alternativeServiceSubType) {
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
	protected function getMetadata(Resource\File $file, $serviceSubType) {
		$data = array();
		$serviceChain = '';

		/** @var \TYPO3\CMS\Core\Service\AbstractService $serviceObj */
		while (is_object($serviceObj = GeneralUtility::makeInstanceService('metaExtract', $serviceSubType, $serviceChain))) {
			$serviceChain .= ',' . $serviceObj->getServiceKey();

			$storageConfiguration = $file->getStorage()->getConfiguration();
			$fileName = $storageConfiguration['pathType'] === 'relative' ? PATH_site : '';
			$fileName .= rtrim($storageConfiguration['basePath'], '/') . $file->getIdentifier();

			$serviceObj->setInputFile($fileName, $file->getProperty('extension'));
			if ($serviceObj->process()) {
				$output = $serviceObj->getOutput();
				if (is_array($output)) {
					// Existing data has precedence over new information, due to service's precedence
					$data = array_merge_recursive($output, $data);
				}
			}
		}

		$data = $this->normalizeMetadata($data);
		return $data;
	}

	/**
	 * Noramlizes the metadata.
	 *
	 * @param array $data
	 * @return array
	 */
	protected function normalizeMetadata(array $data) {
		if (isset($data['fields'])) {
			$data = array_merge_recursive($data, $data['fields']);
			unset($data['fields']);
		}
		if (isset($data['meta'])) {
			foreach ($data['meta'] as $type => $info) {
				foreach ($info as $key => $value) {
					$data[$type . '_' . $key] = $value;
				}
			}
			unset($data['meta']);
		}
		return $data;
	}

}
