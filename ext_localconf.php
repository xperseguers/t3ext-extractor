<?php
defined('TYPO3') || die();

(static function (string $_EXTKEY) {
    $extractorRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::class);

    $settings = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get($_EXTKEY) ?? [];
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
})('extractor');
