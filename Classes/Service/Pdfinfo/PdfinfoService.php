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

namespace Causal\Extractor\Service\Pdfinfo;

use Causal\Extractor\Service\AbstractService;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A Pdfinfo service implementation.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PdfinfoService extends AbstractService
{
    /**
     * PdfinfoService constructor.
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher = null)
    {
        parent::__construct($eventDispatcher);

        $pdfInfo = $this->getPdfInfo();
        if (!is_file($pdfInfo)) {
            throw new \RuntimeException(
                'Invalid path or filename for Pdfinfo: ' . $this->settings['tools_pdfinfo'],
                1445271483
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
        return ['pdf'];
    }

    /**
     * Takes a file reference and extracts its metadata.
     *
     * @param string $fileName Path to the file
     * @return array
     */
    public function extractMetadataFromLocalFile($fileName)
    {
        $pdfinfoCommand = $this->getPdfInfo() . ' -enc UTF-8 ' . CommandUtility::escapeShellArgument($fileName);

        $shellOutput = [];
        CommandUtility::exec($pdfinfoCommand, $shellOutput);

        static::getLogger()->debug(
            'Executing external script',
            [
                'commmand' => $pdfinfoCommand,
                'output' => $shellOutput,
            ]
        );

        $metadata = [];
        foreach ($shellOutput as $line) {
            list($key, $value) = GeneralUtility::trimExplode(':', $line, false, 2);
            $metadata[$key] = $value;
        }

        return $metadata;
    }

    /**
     * Returns the path to the pdfinfo utility.
     *
     * @return string
     */
    protected function getPdfInfo()
    {
        $pdfInfo = is_file($this->settings['tools_pdfinfo'])
            ? $this->settings['tools_pdfinfo']
            : GeneralUtility::getFileAbsFileName($this->settings['tools_pdfinfo']);
        return $pdfInfo;
    }
}
