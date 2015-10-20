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

        $exifTool = GeneralUtility::getFileAbsFileName($this->settings['tools_exiftool'], false);
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
    public function getSupportedFileTypes()
    {
        $exifToolCommand = GeneralUtility::getFileAbsFileName($this->settings['tools_exiftool'], false)
            . ' -listr';

        $shellOutput = array();
        CommandUtility::exec($exifToolCommand, $shellOutput);

        // Remove first line "Recognized file extensions:"
        array_shift($shellOutput);

        $fileTypes = array();
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
        $exifToolCommand = GeneralUtility::getFileAbsFileName($this->settings['tools_exiftool'], false)
            . ' -j'
            . ' ' . escapeshellarg($fileName);

        $shellOutput = array();
        CommandUtility::exec($exifToolCommand, $shellOutput);
        $metadata = json_decode(implode('', $shellOutput), true);

        return $metadata[0];
    }

}
