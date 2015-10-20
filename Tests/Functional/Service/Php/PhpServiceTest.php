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

namespace Causal\Extractor\Tests\Functional\Service\Php;

use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Test cases for class \Causal\Extractor\Service\Php\PhpService.
 */
class PhpServiceTest extends \Causal\Extractor\Tests\Functional\AbstractFunctionalTestCase
{

    /**
     * @var \Causal\Extractor\Service\Php\PhpService
     */
    protected $service;

    /**
     * Set up the service.
     *
     * @return void
     */
    public function setUp()
    {
        $this->service = new \Causal\Extractor\Service\Php\PhpService();
    }

    /**
     * @dataProvider colorSpaceProvider
     * @test
     */
    public function extractColorSpace($fileName, $expectedColorSpace)
    {
        // TODO: remove this once implemented (when?)
        switch (PathUtility::basename($fileName)) {
            case 'colorspace-grey.gif':
            case 'colorspace-grey.png':
            case 'colorspace-yuv.jpg':
                $this->markTestSkipped('must be revisited.');
                break;
        }

        $metadata = $this->service->extractMetadataFromLocalFile($fileName);
        $this->assertSame(strtolower($metadata['ColorSpace']), $expectedColorSpace);
    }

    /**
     * @return array
     */
    public function colorSpaceProvider()
    {
        $assets = $this->getFixtureAssets('colorspace');

        $provider = array();
        foreach ($assets as $fileName) {
            $file = PathUtility::basename($fileName);
            if (preg_match('/^colorspace-([a-z]+)/', $file, $matches)) {
                $provider[$file] = array(
                    $fileName,
                    $matches[1]
                );
            }
        }

        return $provider;
    }

}
