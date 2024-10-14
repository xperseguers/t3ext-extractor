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

namespace Causal\Extractor\Utility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Category helper class.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class CategoryHelper
{

    /**
     * Processes the categories.
     *
     * @param File $file
     * @param array $metadata
     */
    public static function processCategories(File $file, array $metadata): void
    {
        $categories = [];
        $categoryUids = [];
        $key = '__categories__';
        $keyUid = '__category_uids__';
        if (isset($metadata[$key])) {
            $categories = GeneralUtility::trimExplode(',', $metadata[$key], true);
        }
        if (isset($metadata[$keyUid])) {
            $categoryUids = GeneralUtility::intExplode(',', $metadata[$keyUid], true);
        }

        if ((empty($categories) && empty($categoryUids)) || $file->getUid() === 0) {
            return;
        }

        // Since TYPO3 v10, the sys_file_metadata record is not yet available at this point
        $file->getMetaData()->save();

        // Fetch the uid associated to the corresponding sys_file_metadata record
        $statement = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_file_metadata')
            ->select(
                ['uid'],
                'sys_file_metadata',
                [
                    'file' => $file->getUid(),
                    'sys_language_uid' => 0,
                ]
            );
        $row = $statement->fetchAssociative();
        if (!$row) {
            // An error occurred, cannot proceed!
            return;
        }
        $fileMetadataUid = $row['uid'];
        $sorting = 1;
        $data = [];

        // Remove currently associated categories for this file
        $relationTable = 'sys_category_record_mm';
        $tableConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($relationTable);
        $tableConnection->delete(
            $relationTable,
            [
                'uid_foreign' => $fileMetadataUid,
                'tablenames' => 'sys_file_metadata',
                'fieldname' => 'categories',
            ]
        );

        if (!empty($categories)) {
            $statement = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('sys_category')
                ->select(
                    ['uid', 'title'],
                    'sys_category',
                    [
                        'sys_language_uid' => 0,
                    ]
                );
            $typo3Categories = $statement->fetchAllAssociative();

            foreach ($categories as $category) {
                foreach ($typo3Categories as $typo3Category) {
                    if ($typo3Category['title'] === $category
                        || (string)$typo3Category['uid'] === $category
                    ) {
                        $categoryUid = (int)$typo3Category['uid'];
                        $data[$categoryUid] = [
                            'uid_local' => $categoryUid,
                            'uid_foreign' => $fileMetadataUid,
                            'tablenames' => 'sys_file_metadata',
                            'fieldname' => 'categories',
                            'sorting_foreign' => $sorting++,
                        ];
                    }
                }
            }
        }

        foreach ($categoryUids as $categoryUid) {
            $data[$categoryUid] = [
                'uid_local' => $categoryUid,
                'uid_foreign' => $fileMetadataUid,
                'tablenames' => 'sys_file_metadata',
                'fieldname' => 'categories',
                'sorting_foreign' => $sorting++,
            ];
        }

        if (!empty($data)) {
            $tableConnection->bulkInsert(
                $relationTable,
                array_values($data),
                ['uid_local', 'uid_foreign', 'tablenames', 'fieldname', 'sorting_foreign']
            );
        }
    }

}