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

namespace Causal\Extractor\Service\ExifTool;

use Causal\Extractor\Service\AbstractService;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * An ExifTool service implementation.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ExifToolService extends AbstractService
{
    /**
     * ExifToolService constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $exifTool = $this->getExifTool();
        if (!is_file($exifTool)) {
            throw new \RuntimeException(
                'Invalid path or filename for ExifTool: ' . $this->settings['tools_exiftool'],
                1445266821
            );
        }
        if (!is_executable($exifTool)) {
            throw new \RuntimeException(
                'ExifTool is not executable: ' . $this->settings['tools_exiftool'],
                1445267360
            );
        }
    }

    /**
     * Returns a list of supported file types.
     *
     * @return array
     */
    public function getSupportedFileExtensions()
    {
        $exifToolCommand = $this->getExifTool() . ' -listr';

        $shellOutput = [];
        CommandUtility::exec($exifToolCommand, $shellOutput);

        // Remove first line "Recognized file extensions:"
        array_shift($shellOutput);

        $fileTypes = [];
        foreach ($shellOutput as $line) {
            $extensions = GeneralUtility::trimExplode(' ', strtolower($line), true);
            $fileTypes = array_merge($fileTypes, $extensions);
        }

        return $fileTypes;
    }

    /**
     * Takes a file reference and extracts its metadata.
     *
     * @param string $fileName Path to the file
     * @return array
     */
    public function extractMetadataFromLocalFile($fileName)
    {
        $exifToolCommand = $this->getExifTool() . ' -j ' . CommandUtility::escapeShellArgument($fileName);

        $shellOutput = [];
        CommandUtility::exec($exifToolCommand, $shellOutput);

        static::getLogger()->debug(
            'Executing external script',
            [
                'commmand' => $exifToolCommand,
                'output' => $shellOutput,
            ]
        );

        $metadata = json_decode(implode('', $shellOutput), true);

        if (is_array($metadata[0]['Creator'])) {
            $metadata[0]['Creator'] = end($metadata[0]['Creator']);
        }

        return $metadata[0];
    }

    /**
     * Returns the path to the exiftool utility.
     *
     * @return string
     */
    protected function getExifTool()
    {
        if (version_compare(TYPO3_version, '8.0', '>=')) {
            $exifTool = is_file($this->settings['tools_exiftool'])
                ? $this->settings['tools_exiftool']
                : GeneralUtility::getFileAbsFileName($this->settings['tools_exiftool']);
        } else {
            $exifTool = GeneralUtility::getFileAbsFileName($this->settings['tools_exiftool'], false);
        }
        return $exifTool;
    }
}
