<?php
defined('TYPO3_MODE') || die();

if (TYPO3_MODE === 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['sv']['foo'] = array(
        'title' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_reports.xlf:report_title',
        'description' => 'LLL:EXT:extractor/Resources/Private/Language/locallang_reports.xlf:report_description',
        'icon' => 'EXT:extractor/Resources/Public/Images/tx_sv_report.png',
        'report' => 'Causal\\Extractor\\Report\\ServicesListReport'
    );
}
