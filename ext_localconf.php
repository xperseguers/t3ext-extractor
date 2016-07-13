<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {
    $extractorRegistry = \TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::getInstance();

    $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);
    if (is_array($settings)) {
        if (isset($settings['enable_tika']) && (bool)$settings['enable_tika']) {
            $extractorRegistry->registerExtractionService(\Causal\Extractor\Service\Extraction\TikaMetadataExtraction::class);
            $extractorRegistry->registerExtractionService(\Causal\Extractor\Service\Extraction\TikaLanguageDetector::class);
        }
        // Backward compatibility with removed combined option
        $externalTools = !isset($settings['enable_tools_exiftool']) && isset($settings['enable_tools']) && (bool)$settings['enable_tools'];
        if ($externalTools || (isset($settings['enable_tools_exiftool']) && (bool)$settings['enable_tools_exiftool'])) {
            $extractorRegistry->registerExtractionService(\Causal\Extractor\Service\Extraction\ExifToolMetadataExtraction::class);
        }
        if ($externalTools || (isset($settings['enable_tools_pdfinfo']) && (bool)$settings['enable_tools_pdfinfo'])) {
            $extractorRegistry->registerExtractionService(\Causal\Extractor\Service\Extraction\PdfinfoMetadataExtraction::class);
        }
        // Mind the "!isset" in test below to be backward compatible
        if (!isset($settings['enable_php']) || (bool)$settings['enable_php']) {
            $extractorRegistry->registerExtractionService(\Causal\Extractor\Service\Extraction\PhpMetadataExtraction::class);
        }
    }

    if (version_compare(TYPO3_version, '7.5', '<') && isset($settings['auto_extract']) && (bool)$settings['auto_extract']) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_extfilefunc.php']['processData'][] = \Causal\Extractor\Hook\FileUploadHook::class;
    }

    /* ===========================================================================
        Register post-processors (only useful for mapping helper tool in EM)
    =========================================================================== */
    $GLOBALS['TYPO3_CONF_VARS']['EXT'][$_EXTKEY]['processors'] = [
        'Causal\\Extractor\\Utility\\Array_::concatenate(\', \')',
        'Causal\\Extractor\\Utility\\ColorSpace::normalize',
        'Causal\\Extractor\\Utility\\DateTime::timestamp',
        'Causal\\Extractor\\Utility\\Dimension::extractHeight',
        'Causal\\Extractor\\Utility\\Dimension::extractWidth',
        'Causal\\Extractor\\Utility\\Dimension::extractUnit',
        'Causal\\Extractor\\Utility\\Duration::normalize',
        'Causal\\Extractor\\Utility\\Gps::toDecimal',
        'Causal\\Extractor\\Utility\\Number::castInteger',
        'Causal\\Extractor\\Utility\\Number::extractFloat',
        'Causal\\Extractor\\Utility\\Number::extractIntegerAtEnd',
        'Causal\\Extractor\\Utility\\String::trim',
    ];
};

$boot($_EXTKEY);
unset($boot);
