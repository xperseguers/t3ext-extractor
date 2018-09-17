<?php
/**
 * Definitions for routes provided by EXT:extractor
 * Contains all AJAX-based routes for entry points
 */
return [

    // Simulate a club member in Frontend
    'extractor_analyze' => [
        'path' => '/extractor/analyze',
        'target' => \Causal\Extractor\Em\AjaxController::class . '::analyze'
    ],

    // Switch to a member in Backend
    'extractor_process' => [
        'path' => '/extractor/process',
        'target' => \Causal\Extractor\Em\AjaxController::class . '::process'
    ],

];
