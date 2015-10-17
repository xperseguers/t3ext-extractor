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

namespace Causal\Extractor\Service\Tika;

use TYPO3\CMS\Core\Resource\File;

/**
 * A common interface for the different ways of accessing Tika,
 * e.g., standalone application or server.
 *
 * @category    Service/Tika
 * @package     TYPO3
 * @subpackage  tx_extractor
 * @author      Ingo Renner <ingo@typo3.org>
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
interface TikaServiceInterface
{

    /**
     * Returns the version of Tika.
     *
     * @return string
     */
    public function getTikaVersion();

    /**
     * Takes a file reference and extracts the text from it.
     *
     * @param \TYPO3\CMS\Core\Resource\File $file
     * @return string
     */
    public function extractText(File $file);

    /**
     * Takes a file reference and extracts its metadata.
     *
     * @param \TYPO3\CMS\Core\Resource\File $file
     * @return array
     */
    public function extractMetadata(File $file);

    /**
     * Takes a file reference and detects its content's language.
     *
     * @param \TYPO3\CMS\Core\Resource\File $file
     * @return string Language ISO code
     */
    public function detectLanguageFromFile(File $file);

    /**
     * Takes a string as input and detects its language.
     *
     * @param string $input
     * @return string Language ISO code
     */
    public function detectLanguageFromString($input);

}
