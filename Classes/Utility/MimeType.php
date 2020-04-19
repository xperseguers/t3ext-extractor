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

namespace Causal\Extractor\Utility;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * MIME type utility class.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class MimeType
{
    /**
     * @var array
     */
    protected static $mimeTypesMapping = [];

    /**
     * @var array
     */
    protected static $extensionsMapping = [];

    /**
     * Returns an array of file extensions associated to a given mime type.
     *
     * @param string $mimeType
     * @return array
     */
    public static function getFileExtensions($mimeType)
    {
        if (empty(static::$mimeTypesMapping)) {
            static::initialize();
        }

        $extensions = [];
        if (isset(static::$mimeTypesMapping[strtolower($mimeType)])) {
            $extensions = static::$mimeTypesMapping[strtolower($mimeType)];
        }

        return $extensions;
    }

    /**
     * Returns the mime type associated to a given file extension.
     *
     * @param string $fileExtension
     * @return string
     */
    public static function getMimeType($fileExtension)
    {
        if (empty(static::$extensionsMapping)) {
            static::initialize();
        }

        $mimeType = '';
        if (isset(static::$extensionsMapping[strtolower($fileExtension)])) {
            $mimeType = static::$extensionsMapping[strtolower($fileExtension)];
        }

        return $mimeType;
    }

    /**
     * Initializes the mapping between mime types and extensions.
     *
     * @return void
     */
    private static function initialize()
    {
        $fileName = ExtensionManagementUtility::extPath('extractor') . 'Resources/Private/mime.types';
        $fh = fopen($fileName, 'r');
        if (is_resource($fh)) {
            while (($buffer = fgets($fh, 1024)) !== false) {
                if (substr($buffer, 0, 1) === '#') {
                    continue;
                }
                list($mimeType, $extensions) = GeneralUtility::trimExplode("\t", $buffer, true);
                $extensions = GeneralUtility::trimExplode(' ', $extensions, true);
                static::$mimeTypesMapping[$mimeType] = $extensions;
                foreach ($extensions as $extension) {
                    static::$extensionsMapping[$extension] = $mimeType;
                }
            }
            fclose($fh);
        }
    }
}
