<?php
defined('TYPO3_MODE') || defined('TYPO3') || die();

$typo3Branch = class_exists(\TYPO3\CMS\Core\Information\Typo3Version::class)
    ? (new \TYPO3\CMS\Core\Information\Typo3Version())->getBranch()
    : TYPO3_branch;

$tempColumns = [
    'camera_make' => [
        'exclude' => true,
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
        'exclude' => true,
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
        'exclude' => true,
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
        'exclude' => true,
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
        'exclude' => true,
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
        'exclude' => true,
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
        'exclude' => true,
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
        'exclude' => true,
        'l10n_mode' => 'exclude',
        'l10n_display' => 'defaultAsReadonly',
        'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.iso_speed',
        'config' => version_compare($typo3Branch, '12.4', '>=') 
        ? [
            'type' => 'number',
            'readOnly' => true,
        ] 
        : [
            'type' => 'input',
            'size' => '10',
            'eval' => 'int',
            'readOnly' => true,
        ],
    ],
    'aperture' => [
        'exclude' => true,
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
        'exclude' => true,
        'l10n_mode' => 'exclude',
        'l10n_display' => 'defaultAsReadonly',
        'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'default' => '-1',
            'items' => version_compare($typo3Branch, '12.4', '>=') 
            ? [
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.0', 'value' => '0'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.1', 'value' => '1'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.5', 'value' => '5'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.7', 'value' => '7'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.9', 'value' => '9'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.13', 'value' => '13'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.15', 'value' => '15'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.16', 'value' => '16'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.24', 'value' => '24'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.25', 'value' => '25'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.29', 'value' => '29'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.31', 'value' => '31'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.32', 'value' => '32'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.65', 'value' => '65'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.69', 'value' => '69'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.71', 'value' => '71'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.73', 'value' => '73'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.77', 'value' => '77'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.79', 'value' => '79'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.89', 'value' => '89'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.93', 'value' => '93'],
                ['label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.flash.95', 'value' => '95'],
            ]
            : [
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
        'exclude' => true,
        'l10n_mode' => 'exclude',
        'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.altitude',
        'config' => version_compare($typo3Branch, '12.4', '>=') 
        ? [
            'type' => 'number',
        ] 
        : [
            'type' => 'input',
            'size' => '10',
            'eval' => 'int',
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_metadata', $tempColumns);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'sys_file_metadata',
    'camera',
    'camera_make, camera_model' );

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'sys_file_metadata',
    '--palette--;;camera,
		shutter_speed, aperture, exposure_bias_value,
		iso_speed,
		focal_length, camera_lens,
		flash,
		color_space, white_balance_mode,',
    \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE,
    'replace:color_space'
);

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
