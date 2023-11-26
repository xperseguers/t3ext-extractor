<?php
defined('TYPO3_MODE') || defined('TYPO3') || die();

// Additional metadata
$tca = [
    'types' => [
        TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
            'showitem' => '
                    fileinfo, title, description, ranking, keywords,
                        --palette--;LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:palette.accessibility;20,
                    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,
                        --palette--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:palette.visibility;10,
                        fe_groups,
                    --div--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:tabs.metadata,
                        creator, creator_tool, publisher, source, copyright,
                        --palette--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:palette.geo_location;40,
                        --palette--;LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:palette.gps;30,
                    --div--;LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:tabs.camera,
                        --palette--;;camera,
                        shutter_speed, aperture, exposure_bias_value,
                        iso_speed,
                        focal_length, camera_lens,
                        flash,
                        color_space, white_balance_mode,
                        --palette--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:palette.metrics;50
                ',
        ],
    ],
    'palettes' => [
        'camera' => [
            'showitem' => 'camera_make, camera_model',
        ],
    ],
    'columns' => [
        'camera_make' => [
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.camera_make',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'readOnly' => true,
            ],
        ],
        'camera_model' => [
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.camera_model',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'readOnly' => true,
            ],
        ],
        'camera_lens' => [
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.camera_lens',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'readOnly' => true,
            ],
        ],
        'shutter_speed' => [
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.shutter_speed',
            'config' => [
                'type' => 'input',
                'size' => '10',
                'readOnly' => true,
            ],
        ],
        'focal_length' => [
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.focal_length',
            'config' => [
                'type' => 'input',
                'size' => '10',
                'eval' => 'float',
                'readOnly' => true,
            ],
        ],
        'exposure_bias' => [
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.exposure_bias',
            'config' => [
                'type' => 'input',
                'size' => '20',
                'readOnly' => true,
            ],
        ],
        'white_balance_mode' => [
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.white_balance_mode',
            'config' => [
                'type' => 'input',
                'size' => '20',
                'readOnly' => true,
            ],
        ],
        'iso_speed' => [
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.iso_speed',
            'config' => [
                'type' => 'input',
                'size' => '10',
                'eval' => 'int',
                'readOnly' => true,
            ],
        ],
        'aperture' => [
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.aperture',
            'config' => [
                'type' => 'input',
                'size' => '10',
                'eval' => 'float',
                'readOnly' => true,
            ],
        ],
        'flash' => [
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => '-1',
                'items' => [
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.0', '0'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.1', '1'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.5', '5'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.7', '7'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.9', '9'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.13', '13'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.15', '15'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.16', '16'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.24', '24'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.25', '25'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.29', '29'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.31', '31'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.32', '32'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.65', '65'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.69', '69'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.71', '71'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.73', '73'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.77', '77'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.79', '79'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.89', '89'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.93', '93'],
                    ['LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.95', '95'],
                ],
                'readOnly' => true,
            ],
        ],
        'altitude' => [
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.altitude',
            'config' => [
                'type' => 'input',
                'size' => '10',
                'eval' => 'int',
            ],
        ],
    ],
];

$GLOBALS['TCA']['sys_file_metadata'] = array_replace_recursive($GLOBALS['TCA']['sys_file_metadata'], $tca);

// Add category tab if categories column is present
if (isset($GLOBALS['TCA']['sys_file_metadata']['columns']['categories'])) {
    $typo3Branch = class_exists(\TYPO3\CMS\Core\Information\Typo3Version::class)
        ? (new \TYPO3\CMS\Core\Information\Typo3Version())->getBranch()
        : TYPO3_branch;
    $locallangTca = version_compare($typo3Branch, '9.0', '<')
        ? 'LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf'
        : 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'sys_file_metadata',
        '--div--;' . $locallangTca . ':sys_category.tabs.category,categories'
    );
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'sys_file_metadata',
    '30',
    'altitude'
);

// Reapply possible changes to sys_file_metadata from extensions loaded *before* EXT:extractor
$loadedExtensions = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getLoadedExtensionListArray();
foreach ($loadedExtensions as $extension) {
    if ($extension === 'extractor') {
        break;
    }
    $extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extension);
    $isSystemExtension = strpos($extensionPath, '/sysext/') !== false
        || strpos($extensionPath, '/vendor/typo3/cms-') !== false;
    if (!$isSystemExtension) {
        $overrideTcaFileName = $extensionPath . 'Configuration/TCA/Overrides/sys_file_metadata.php';
        if (is_file($overrideTcaFileName)) {
            include($overrideTcaFileName);
        }
    }
}
