<?php
defined('TYPO3_MODE') || die();

$extractorRegistry = \TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::getInstance();

$settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);
if (is_array($settings)) {
    if (isset($settings['enable_tika']) && (bool)$settings['enable_tika']) {
        $extractorRegistry->registerExtractionService('Causal\\Extractor\\Service\\Extraction\\TikaMetadataExtraction');
        $extractorRegistry->registerExtractionService('Causal\\Extractor\\Service\\Extraction\\TikaLanguageDetector');
    }
    if (isset($settings['enable_tools']) && (bool)$settings['enable_tools']) {
        $extractorRegistry->registerExtractionService('Causal\\Extractor\\Service\\Extraction\\ExifToolMetadataExtraction');
        $extractorRegistry->registerExtractionService('Causal\\Extractor\\Service\\Extraction\\PdfinfoMetadataExtraction');
    }
}

if (isset($settings['auto_extract']) && (bool)$settings['auto_extract']) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_extfilefunc.php']['processData'][] = 'Causal\\Extractor\\Hook\\FileUploadHook';
}

// Cleanup
unset($settings);
unset($extractorRegistry);
