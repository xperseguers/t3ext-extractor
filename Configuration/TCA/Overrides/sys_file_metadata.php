<?php
defined('TYPO3_MODE') || die();

// Additional metadata
$tca = array(
    'types' => array(
        TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array(
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
                        color_space, white_balance_mode,
                        --palette--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:palette.metrics;50
                ',
        ),
    ),
    'palettes' => array(
        'camera' => array(
            'showitem' => 'camera_make, camera_model',
        ),
    ),
    'columns' => array(
        'camera_make' => array(
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.camera_make',
            'config' => array(
                'type' => 'input',
                'size' => '30',
                'readOnly' => true,
            ),
        ),
        'camera_model' => array(
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.camera_model',
            'config' => array(
                'type' => 'input',
                'size' => '30',
                'readOnly' => true,
            ),
        ),
        'camera_lens' => array(
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.camera_lens',
            'config' => array(
                'type' => 'input',
                'size' => '30',
                'readOnly' => true,
            ),
        ),
        'shutter_speed' => array(
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.shutter_speed',
            'config' => array(
                'type' => 'input',
                'size' => '10',
                'readOnly' => true,
            ),
        ),
        'focal_length' => array(
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.focal_length',
            'config' => array(
                'type' => 'input',
                'size' => '10',
                'eval' => 'float',
                'readOnly' => true,
            ),
        ),
        'exposure_bias' => array(
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.exposure_bias',
            'config' => array(
                'type' => 'input',
                'size' => '20',
                'readOnly' => true,
            ),
        ),
        'white_balance_mode' => array(
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.white_balance_mode',
            'config' => array(
                'type' => 'input',
                'size' => '20',
                'readOnly' => true,
            ),
        ),
        'iso_speed' => array(
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.iso_speed',
            'config' => array(
                'type' => 'input',
                'size' => '10',
                'eval' => 'int',
                'readOnly' => true,
            ),
        ),
        'aperture' => array(
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.aperture',
            'config' => array(
                'type' => 'input',
                'size' => '10',
                'eval' => 'float',
                'readOnly' => true,
            ),
        ),
    ),
);

$GLOBALS['TCA']['sys_file_metadata'] = array_replace_recursive($GLOBALS['TCA']['sys_file_metadata'], $tca);

// Add category tab if categories column is present
if (isset($GLOBALS['TCA']['sys_file_metadata']['columns']['categories'])) {
    $locallangTca = version_compare(TYPO3_version, '9.0', '<')
        ? 'LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf'
        : 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'sys_file_metadata',
        '--div--;' . $locallangTca . ':sys_category.tabs.category,categories'
    );
}

// Reapply possible changes to sys_file_metadata from extensions loaded *before* EXT:extractor
$loadedExtensions = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getLoadedExtensionListArray();
foreach ($loadedExtensions as $extension) {
    if ($extension === 'extractor') {
        break;
    }
    $extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extension);
    if (strpos($extensionPath, '/sysext/') === false) {
        $overrideTcaFileName = $extensionPath . 'Configuration/TCA/Overrides/sys_file_metadata.php';
        if (is_file($overrideTcaFileName)) {
            include($overrideTcaFileName);
        }
    }
}
