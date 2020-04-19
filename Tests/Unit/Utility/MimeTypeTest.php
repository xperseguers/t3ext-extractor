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

use Causal\Extractor\Utility\MimeType;

/**
 * Test cases for class \Causal\Extractor\Utility\MimeType.
 */
class MimeTypeTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{

    /**
     * @param string $mimeType
     * @param array $expectedExtensions
     * @dataProvider mimeTypeProvider
     * @test
     */
    public function fetchExtensionByMimeType($mimeType, array $expectedExtensions)
    {
        $extensions = MimeType::getFileExtensions($mimeType);
        $this->assertSame($expectedExtensions, $extensions);
    }

    /**
     * @return array
     */
    public function mimeTypeProvider()
    {
        return [
            'MPEG file' => [
                'audio/mpeg',
                ['mpga', 'mp2', 'mp2a', 'mp3', 'm2a', 'm3a'],
            ],
            'MS Word Document (docx)' => [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ['docx'],
            ],
            'PDF file' => [
                'application/pdf',
                ['pdf'],
            ],
            // Invalid and empty mime type
            'Invalid mime type' => [
                'INVALID-MIME-TYPE',
                []
            ],
            'Empty mime type' => [
                '',
                []
            ],
        ];
    }

    /**
     * @param string $extension
     * @param string $expectedMimeType
     * @dataProvider extensionProvider
     * @test
     */
    public function fetchMimeTypeByExtension($extension, $expectedMimeType)
    {
        $mimeType = MimeType::getMimeType($extension);
        $this->assertSame($expectedMimeType, $mimeType);
    }

    /**
     * @return array
     */
    public function extensionProvider()
    {
        return [
            'mp3' => ['mp3', 'audio/mpeg'],
            'pdf' => ['pdf', 'application/pdf'],
            'jpg' => ['jpg', 'image/jpeg'],
        ];
    }

}