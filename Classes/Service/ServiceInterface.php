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

namespace Causal\Extractor\Service;

use TYPO3\CMS\Core\Resource\File;

/**
 * A common interface for the different ways of extracting metadata
 * from files.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
interface ServiceInterface
{

    /**
     * Returns a list of supported file types.
     *
     * @return array
     */
    public function getSupportedFileTypes();

    /**
     * Takes a file reference and extracts its metadata.
     *
     * @param \TYPO3\CMS\Core\Resource\File $file
     * @return array
     */
    public function extractMetadata(File $file);

    /**
     * Takes a file reference and extracts its metadata.
     *
     * @param string $fileName Path to a file
     * @return array
     */
    public function extractMetadataFromLocalFile($fileName);

}
