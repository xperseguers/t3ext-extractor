<?php
defined('TYPO3_MODE') || die();

if (TYPO3_MODE === 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['sv']['extractor'] = array(
        'title' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_reports.xlf:report_title',
        'description' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_reports.xlf:report_description',
        'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Images/tx_sv_report.png',
        'report' => \Causal\Extractor\Report\ServicesListReport::class
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
        $_EXTKEY . '::analyze',
        \Causal\Extractor\Em\AjaxController::class . '->analyze'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
        $_EXTKEY . '::process',
        \Causal\Extractor\Em\AjaxController::class . '->process'
    );
}
