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

$EM_CONF[$_EXTKEY] = [
    'title' => 'Metadata and content analysis service',
    'description' => 'This extension detects and extracts metadata (EXIF / IPTC / XMP / ...) from potentially thousand different file types (such as MS Word/Powerpoint/Excel documents, PDF and images) and bring them automatically and natively to TYPO3 when uploading assets. Works with built-in PHP functions but takes advantage of Apache Tika and other external tools for enhanced metadata extraction.',
    'category' => 'services',
    'author' => 'Xavier Perseguers',
    'author_company' => 'Causal Sàrl',
    'author_email' => 'xavier@causal.ch',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '2.0.0-dev',
    'constraints' => [
        'depends' => [
            'php' => '7.2.0-7.4.99',
            'typo3' => '8.7.0-11.0.99',
            'filemetadata' => '',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
