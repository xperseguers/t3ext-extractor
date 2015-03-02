<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::getInstance()->registerExtractionService('Causal\\Extractor\\Service\\Bridge');

$extractorConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);
if (isset($extractorConfiguration['auto_extract']) && (bool)$extractorConfiguration['auto_extract']) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_extfilefunc.php']['processData'][] = 'Causal\\Extractor\\Hook\\FileUploadHook';
}
unset($extractorConfiguration);
