<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "extractor".
 *
 * Auto generated 17-10-2015 11:00
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Metadata and content analysis service',
    'description' => 'This extension detects and extracts metadata (EXIF / IPTC / XMP / ...) from potentially thousand different file types (such as MS Word/Powerpoint/Excel documents, PDF and images) and bring them automatically and natively to TYPO3 when uploading assets. Works with built-in PHP functions but takes advantage of Apache Tika and other external tools for enhanced metadata extraction.',
    'category' => 'services',
    'author' => 'Xavier Perseguers (Causal)',
    'author_company' => 'Causal Sàrl',
    'author_email' => 'xavier@causal.ch',
    'shy' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'version' => '1.1.0-dev',
    'constraints' => array(
        'depends' => array(
            'php' => '5.3.3-5.6.99',
            'typo3' => '6.2.0-7.99.99',
            'filemetadata' => '',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
    '_md5_values_when_last_written' => '',
);
