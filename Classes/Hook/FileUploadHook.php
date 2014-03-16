<?php
namespace Causal\Extractor\Hook;

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
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * This class allows metadata to be extracted automatically after
 * uploading a file.
 *
 * @category    Hook
 * @package     TYPO3
 * @subpackage  tx_extractor
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class FileUploadHook implements \TYPO3\CMS\Core\Utility\File\ExtendedFileUtilityProcessDataHookInterface {

	/**
	 * @var \TYPO3\CMS\Core\Resource\Index\ExtractorInterface[]
	 */
	static protected $extractionServices = NULL;

	/**
	 * @param string $action The action
	 * @param array $cmdArr The parameter sent to the action handler
	 * @param array $result The results of all calls to the action handler
	 * @param \TYPO3\CMS\Core\Utility\File\ExtendedFileUtility $pObj The parent object
	 * @return void
	 */
	public function processData_postProcessAction($action, array $cmdArr, array $result, \TYPO3\CMS\Core\Utility\File\ExtendedFileUtility $pObj) {
		if ($action === 'upload') {
			/** @var \TYPO3\CMS\Core\Resource\File[] $fileObjects */
			$fileObjects = array_pop($result);
			if (!is_array($fileObjects)) {
				return;
			}

			foreach ($fileObjects as $fileObject) {
				$storageRecord = $fileObject->getStorage()->getStorageRecord();
				if ($storageRecord['driver'] === 'Local') {
					$this->runMetaDataExtraction($fileObject);
				}
			}
		}
	}

	/**
	 * Runs the metadata extraction for a given file.
	 *
	 * @param \TYPO3\CMS\Core\Resource\File $fileObject
	 * @return void
	 * @see \TYPO3\CMS\Core\Resource\Index\Indexer::runMetaDataExtraction
	 */
	protected function runMetaDataExtraction(\TYPO3\CMS\Core\Resource\File $fileObject) {
		if (static::$extractionServices === NULL) {
			$extractorRegistry = \TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::getInstance();
			static::$extractionServices = $extractorRegistry->getExtractorsWithDriverSupport('Local');
		}

		$newMetaData = array(
			0 => $fileObject->_getMetaData()
		);
		foreach (static::$extractionServices as $service) {
			if ($service->canProcess($fileObject)) {
				$newMetaData[$service->getPriority()] = $service->extractMetaData($fileObject, $newMetaData);
			}
		}
		ksort($newMetaData);
		$metaData = array();
		foreach ($newMetaData as $data) {
			$metaData = array_merge($metaData, $data);
		}
		$fileObject->_updateMetaDataProperties($metaData);
		$metaDataRepository = \TYPO3\CMS\Core\Resource\Index\MetaDataRepository::getInstance();
		$metaDataRepository->update($fileObject->getUid(), $metaData);
	}

}
