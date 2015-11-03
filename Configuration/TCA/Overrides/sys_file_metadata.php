<?php
defined('TYPO3_MODE') || die();

// This file is a backport of EXT:filemetadata in TYPO3 v7
if (version_compare(TYPO3_version, '7.6.0', '<')) {
    $tcaV7 = array(
        'ctrl' => array(
            'type' => 'file:type',
        ),
        'types' => array(
            TYPO3\CMS\Core\Resource\File::FILETYPE_UNKNOWN => array(
                'showitem' => '
                    fileinfo, title, description, ranking, keywords,
                        --palette--;LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:palette.accessibility;25,
                    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,
                        --palette--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:palette.visibility;10,
                        fe_groups,
                    --div--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:tabs.metadata,
                        creator, creator_tool, publisher, source, copyright,
                        --palette--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:palette.geo_location;40
                ',
            ),
            TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => array(
                'showitem' => '
                    fileinfo, title, description, ranking, keywords,
                        --palette--;LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:palette.accessibility;25,
                    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,
                        --palette--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:palette.visibility;10,
                        fe_groups,
                    --div--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:tabs.metadata,
                        creator, creator_tool, publisher, source, copyright,
                        --palette--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:palette.geo_location;40,
                        language
                ',
            ),
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
                        color_space,
                        --palette--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:palette.metrics;50
                ',
            ),
            TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => array(
                'showitem' => '
                    fileinfo, title, description, ranking, keywords,
                        --palette--;LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:palette.accessibility;25,
                    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,
                        --palette--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:palette.visibility;10,
                        fe_groups,
                    --div--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:tabs.metadata,
                        creator, creator_tool, publisher, source, copyright, language,
                    --div--;LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:tabs.audio,
                        duration
                ',
            ),
            TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => array(
                'showitem' => '
                    fileinfo, title, description, ranking, keywords,
                        --palette--;LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:palette.accessibility;25,
                    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,
                        --palette--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:palette.visibility;10,
                        fe_groups,
                    --div--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:tabs.metadata,
                        creator, creator_tool, publisher, source, copyright, language,
                    --div--;LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:tabs.video,
                        duration
                ',
            ),
            TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => array(
                'showitem' => '
                    fileinfo, title, description, ranking, keywords,
                        --palette--;LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:palette.accessibility;25,
                    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,
                        --palette--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:palette.visibility;10,
                        fe_groups,
                    --div--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:tabs.metadata,
                        creator, creator_tool, publisher, source, copyright,
                        --palette--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:palette.geo_location;40,
                        language, pages,
                        --palette--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:palette.metrics;50,
                        --palette--;LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:palette.content_date;60
                ',
            ),
        ),
        'palettes' => array(
            '10' => array(
                'showitem' => 'visible, status',
            ),
            '20' => array(
                'showitem' => 'alternative, --linebreak--, caption, --linebreak--, download_name',
            ),
            '25' => array(
                'showitem' => 'caption, --linebreak--, download_name',
            ),
            '30' => array(
                'showitem' => 'latitude, longitude',
            ),
            '40' => array(
                'showitem' => 'location_country, location_region, location_city',
            ),
            '50' => array(
                'showitem' => 'width, height, unit',
            ),
            '60' => array(
                'showitem' => 'content_creation_date, content_modification_date',
            ),
        ),
        'columns' => array(
            'visible' => array(
                'exclude' => 1,
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.visible',
                'config' => array(
                    'type' => 'check',
                    'default' => '1'
                ),
            ),
            'status' => array(
                'exclude' => 1,
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.status',
                'config' => array(
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => array(
                        array(
                            'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.status.1',
                            1,
                            'EXT:filemetadata/Resources/Public/Icons/status_1.png'
                        ),
                        array(
                            'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.status.2',
                            2,
                            'EXT:filemetadata/Resources/Public/Icons/status_2.png'
                        ),
                        array(
                            'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.status.3',
                            3,
                            'EXT:filemetadata/Resources/Public/Icons/status_3.png'
                        ),
                    ),
                    'showIconTable' => true,
                ),
            ),
            'keywords' => array(
                'exclude' => 1,
                'l10n_mode' => 'prefixLangTitle',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.keywords',
                'config' => array(
                    'type' => 'text',
                    'cols' => '40',
                    'rows' => '3',
                    'placeholder' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:placeholder.keywords'
                ),
            ),
            'caption' => array(
                'exclude' => 1,
                'l10n_mode' => 'prefixLangTitle',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.caption',
                'config' => array(
                    'type' => 'input',
                    'size' => 30,
                    'eval' => 'trim'
                ),
            ),
            'creator_tool' => array(
                'exclude' => 1,
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.creator_tool',
                'config' => array(
                    'type' => 'input',
                    'size' => 20,
                    'eval' => 'trim'
                ),
            ),
            'download_name' => array(
                'exclude' => 1,
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.download_name',
                'config' => array(
                    'type' => 'input',
                    'size' => 30,
                    'eval' => 'trim'
                ),
            ),
            'creator' => array(
                'exclude' => 1,
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.creator',
                'config' => array(
                    'type' => 'input',
                    'size' => 30,
                    'eval' => 'trim'
                ),
            ),
            'publisher' => array(
                'exclude' => 1,
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.publisher',
                'config' => array(
                    'type' => 'input',
                    'size' => 20,
                    'eval' => 'trim'
                ),
            ),
            'source' => array(
                'exclude' => 1,
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.source',
                'config' => array(
                    'type' => 'input',
                    'size' => 20,
                    'eval' => 'trim'
                ),
            ),
            'copyright' => array(
                'exclude' => 1,
                'label' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.copyright',
                'config' => array(
                    'type' => 'input',
                    'size' => 20,
                    'eval' => 'trim'
                ),
            ),
            'location_country' => array(
                'exclude' => 1,
                'l10n_mode' => 'mergeIfNotBlank',
                'l10n_display' => '',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.location_country',
                'config' => array(
                    'type' => 'input',
                    'size' => 20,
                    'eval' => 'trim'
                ),
            ),
            'location_region' => array(
                'exclude' => 1,
                'l10n_mode' => 'mergeIfNotBlank',
                'l10n_display' => '',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.location_region',
                'config' => array(
                    'type' => 'input',
                    'size' => 20,
                    'eval' => 'trim'
                ),
            ),
            'location_city' => array(
                'exclude' => 1,
                'l10n_mode' => 'mergeIfNotBlank',
                'l10n_display' => '',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.location_city',
                'config' => array(
                    'type' => 'input',
                    'size' => 20,
                    'eval' => 'trim'
                ),
            ),
            'latitude' => array(
                'exclude' => 1,
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.latitude',
                'config' => array(
                    'type' => 'input',
                    'size' => '20',
                    'eval' => 'trim',
                    'max' => '30',
                    'default' => '0.00000000000000'
                ),
            ),
            'longitude' => array(
                'exclude' => 1,
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.longitude',
                'config' => array(
                    'type' => 'input',
                    'size' => '20',
                    'eval' => 'trim',
                    'max' => '30',
                    'default' => '0.00000000000000'
                ),
            ),
            'ranking' => array(
                'exclude' => 1,
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.ranking',
                'config' => array(
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'minitems' => 1,
                    'maxitems' => 1,
                    'items' => array(
                        array(0, 0),
                        array(1, 1),
                        array(2, 2),
                        array(3, 3),
                        array(4, 4),
                        array(5, 5),
                    ),
                ),
            ),
            'content_creation_date' => array(
                'exclude' => 1,
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.content_creation_date',
                'config' => array(
                    'type' => 'input',
                    'size' => 12,
                    'max' => 20,
                    'eval' => 'date',
                    'checkbox' => 1,
                    'default' => time()
                ),
            ),
            'content_modification_date' => array(
                'exclude' => 1,
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.content_modification_date',
                'config' => array(
                    'type' => 'input',
                    'size' => 12,
                    'max' => 20,
                    'eval' => 'date',
                    'checkbox' => 1,
                    'default' => time()
                ),
            ),
            'note' => array(
                'exclude' => 1,
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.note',
                'config' => array(
                    'type' => 'text',
                    'cols' => 40,
                    'rows' => 15,
                    'eval' => 'trim'
                ),
            ),
            /*
             * METRICS ###########################################
             */
            'unit' => array(
                'exclude' => 1,
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.unit',
                'config' => array(
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => array(
                        array('', ''),
                        array('LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.unit.px', 'px'),
                        array('LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.unit.cm', 'cm'),
                        array('LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.unit.in', 'in'),
                        array('LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.unit.mm', 'mm'),
                        array('LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.unit.m', 'm'),
                        array('LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.unit.p', 'p'),
                        array('LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.unit.pt', 'pt')
                    ),
                    'default' => '',
                    'readOnly' => true,
                ),
            ),
            'duration' => array(
                'exclude' => 1,
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.duration',
                'config' => array(
                    'type' => 'input',
                    'size' => '10',
                    'max' => '20',
                    'eval' => 'int',
                    'default' => '0'
                )
            ),
            'color_space' => array(
                'exclude' => 1,
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.color_space',
                'config' => array(
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => array(
                        array('', ''),
                        array('LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.color_space.RGB', 'RGB'),
                        array('LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.color_space.sRGB', 'sRGB'),
                        array('LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.color_space.CMYK', 'CMYK'),
                        array('LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.color_space.CMY', 'CMY'),
                        array('LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.color_space.YUV', 'YUV'),
                        array('LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.color_space.grey', 'grey'),
                        array('LLL:EXT:extractor/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.color_space.indx', 'indx'),
                    ),
                    'default' => '',
                    'readOnly' => true,
                )
            ),
            'width' => array(
                'exclude' => 1,
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.width',
                'config' => array(
                    'type' => 'input',
                    'size' => '10',
                    'max' => '20',
                    'eval' => 'int',
                    'default' => '0',
                    'readOnly' => true,
                ),
            ),
            'height' => array(
                'exclude' => 1,
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.height',
                'config' => array(
                    'type' => 'input',
                    'size' => '10',
                    'max' => '20',
                    'eval' => 'int',
                    'default' => '0',
                    'readOnly' => true,
                ),
            ),
            'pages' => array(
                'exclude' => 1,
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.pages',
                'config' => array(
                    'type' => 'input',
                    'size' => 30,
                    'eval' => 'trim',
                    'readOnly' => true
                ),
            ),
            'language' => array(
                'exclude' => 1,
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.language',
                'config' => array(
                    'type' => 'input',
                    'size' => 30,
                    'eval' => 'trim'
                )
            ),
            'fe_groups' => array(
                'exclude' => 1,
                'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.fe_group',
                'config' => array(
                    'type' => 'select',
                    'renderType' => 'selectMultipleSideBySide',
                    'size' => 5,
                    'maxitems' => 20,
                    'items' => array(
                        array(
                            'LLL:EXT:lang/locallang_general.xlf:LGL.hide_at_login',
                            -1
                        ),
                        array(
                            'LLL:EXT:lang/locallang_general.xlf:LGL.any_login',
                            -2
                        ),
                        array(
                            'LLL:EXT:lang/locallang_general.xlf:LGL.usergroups',
                            '--div--'
                        )
                    ),
                    'exclusiveKeys' => '-1,-2',
                    'foreign_table' => 'fe_groups',
                    'foreign_table_where' => 'ORDER BY fe_groups.title'
                )
            ),
            'alternative' => array(
                'config' => array(
                    'type' => 'input',
                    'size' => '30',
                ),
            ),
        ),
    );

    $GLOBALS['TCA']['sys_file_metadata'] = array_replace_recursive($GLOBALS['TCA']['sys_file_metadata'], $tcaV7);

    // Add category tab if categories column is present
    if (isset($GLOBALS['TCA']['sys_file_metadata']['columns']['categories'])) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'sys_file_metadata',
            '--div--;LLL:EXT:lang/locallang_tca.xlf:sys_category.tabs.category,categories'
        );
    }

}

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

if (version_compare(TYPO3_version, '6.99.99', '<=')) {
    // EXT:frontend does not exist
    foreach ($GLOBALS['TCA']['sys_file_metadata']['types'] as $type => $configuration) {
        $GLOBALS['TCA']['sys_file_metadata']['types'][$type]['showitem'] = str_replace('LLL:EXT:frontend/Resources/Private/Language/', 'LLL:EXT:cms/', $GLOBALS['TCA']['sys_file_metadata']['types'][$type]['showitem']);
    }

    foreach ($GLOBALS['TCA']['sys_file_metadata']['palettes'] as $palette => $configuration) {
        $GLOBALS['TCA']['sys_file_metadata']['palettes'][$palette]['canNotCollapse'] = 1;
    }
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
