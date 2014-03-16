<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "extractor".
 *
 * Auto generated 15-03-2014 15:08
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TYPO3 Bridge Metadata Extractor for FAL',
	'description' => 'Provides a bridge between TYPO3 metadata extraction services and FAL extractors. Lets you use extensions such as "svmetaextract" or "tika" with FAL. Will automatically populate EXIF / IPTC / XMP and other file metadata extracted from images, videos, sounds or standard files (PDF, DOC, ...) by running the FAL indexer scheduler task.',
	'category' => 'services',
	'author' => 'Xavier Perseguers (Causal)',
	'author_company' => 'Causal Sàrl',
	'author_email' => 'xavier@causal.ch',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '0.1.0',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.3-5.5.99',
			'typo3' => '6.2.0-6.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => '',
	'suggests' => array(
	),
);

?>