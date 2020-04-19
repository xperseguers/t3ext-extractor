<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\Extractor\Tests\Unit\Utility;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test cases for mapping configuration files.
 */
class ConfigurationTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{

    /**
     * @param string $service
     * @test
     * @dataProvider serviceProvider
     */
    public function exifToolMappingIsValid($service)
    {
        $files = $this->getConfigurationFiles($service);
        foreach ($files as $file) {
            $mapping = json_decode(file_get_contents($file));
            $this->assertTrue(is_array($mapping), json_last_error_msg() . ' with file "' . $file . '"');
        }
    }

    /**
     * @return array
     */
    public function serviceProvider()
    {
        return [
            'ExifTool' => ['ExifTool'],
            'Pdfinfo' => ['Pdfinfo'],
            'Php' => ['Php'],
            'Tika' => ['Tika'],
        ];
    }

    /**
     * @param string $service
     * @return array
     */
    protected function getConfigurationFiles($service)
    {
        $configurationPath = ExtensionManagementUtility::extPath('extractor') . 'Configuration/Services/' . $service . '/';
        $files = array_map(
            function ($e) use ($configurationPath) {
                return $configurationPath . $e;
            },
            GeneralUtility::getFilesInDir($configurationPath, 'json')
        );
        return $files;
    }

}