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
     * @param string $fileName
     * @param string $expectedColorSpace
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
        $this->assertSame($expectedColorSpace, strtolower($metadata['ColorSpace']));
    }

    /**
     * @return array
     */
    public function colorSpaceProvider()
    {
        $assets = $this->getFixtureAssets('colorspace');

        $provider = [];
        foreach ($assets as $file => $fileName) {
            if (preg_match('/^colorspace-([a-z]+)/', $file, $matches)) {
                $provider[$file] = [
                    $fileName,
                    $matches[1]
                ];
            }
        }

        return $provider;
    }

    /**
     * @param string $fileName
     * @param array $expectedMetadata
     * @dataProvider officeDocumentProvider
     * @test
     */
    public function extractMetadataFromOfficeDocument($fileName, array $expectedMetadata)
    {
        $keys = [
            'dc:title',
            'dc:subject',
            'dc:creator',
            'Manager',
            'Company',
            'cp:category',
            'cp:keywords',
            'dc:description',
        ];

        $metadata = $this->service->extractMetadataFromLocalFile($fileName);
        foreach ($keys as $key) {
            $this->assertSame($expectedMetadata[$key], $metadata[$key], $key . ' is not the same.');
        }
    }

    /**
     * @return array
     */
    public function officeDocumentProvider()
    {
        $assets = $this->getFixtureAssets('msoffice');

        $provider = [];
        foreach ($assets as $file => $fileName) {
            $provider[$file] = [
                $fileName,
                [
                    'dc:title' => 'The Title',
                    'dc:subject' => 'The Subject',
                    'dc:creator' => 'The Author',
                    'Manager' => 'The Manager',
                    'Company' => 'The Company',
                    'cp:category' => 'The Category',
                    'cp:keywords' => 'The Keywords',
                    'dc:description' => 'The Comments',
                ],
            ];
        }

        return $provider;
    }

    /**
     * @param string $fileName
     * @param array $expectedMetadata
     * @dataProvider pdfProvider
     * @test
     */
    public function extractMetadataFromPdf($fileName, array $expectedMetadata)
    {
        $keys = [
            'Author',
            'Title',
            'Subject',
            'Keywords',
            'Pages',
            'xmp:creator',
            'xmp:CreatorTool',
            'xmp:rights',
            'xmp:title',
        ];

        $metadata = $this->service->extractMetadataFromLocalFile($fileName);
        foreach ($keys as $key) {
            $this->assertSame($expectedMetadata[$key], $metadata[$key], $key . ' is not the same.');
        }
    }

    /**
     * @return array
     */
    public function pdfProvider()
    {
        $assets = $this->getFixtureAssets('pdf');

        $provider = [];
        foreach ($assets as $file => $fileName) {
            $provider[$file] = [
                $fileName,
                [
                    'Author' => 'The Author',
                    'Title' => 'The Title',
                    'Subject' => 'The Subject',
                    'Keywords' => 'The Keywords',
                    'Pages' => 1,
                    'xmp:creator' => 'The Author',
                    'xmp:CreatorTool' => 'Word',
                    'xmp:rights' => 'The Copyright',
                    'xmp:title' => 'The Title',
                ],
            ];
        }

        return $provider;
    }

}
