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
     * @test
     * @param string $mimeType
     * @param array $expected
     * @dataProvider mimeTypeProvider
     */
    public function fetchExtensionByMimeType($mimeType, array $expected)
    {
        $extensions = MimeType::getFileExtensions($mimeType);
        $this->assertSame($expected, $extensions);
    }

    /**
     * @return array
     */
    public function mimeTypeProvider()
    {
        return array(
            array(
                'audio/mpeg',
                array('mpga', 'mp2', 'mp2a', 'mp3', 'm2a', 'm3a'),
            ),
            array(
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                array('docx'),
            ),
            array(
                'application/pdf',
                array('pdf'),
            ),
            // Invalid and empty mime type
            array('INVALID-MIME-TYPE', array()),
            array('', array()),
        );
    }

}