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

use Causal\Extractor\Service\AbstractService;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A Tika service implementation using the tika-app-x.x.jar.
 *
 * @author      Ingo Renner <ingo@typo3.org>
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class AppService extends AbstractService implements TikaServiceInterface
{
    /**
     * AppService constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $tikaJar = $this->getTikaJar();
        if (!is_file($tikaJar)) {
            throw new \RuntimeException(
                'Invalid path or filename for Tika application jar: ' . $this->settings['tika_jar_path'],
                1445096468
            );
        }

        if (!CommandUtility::checkCommand('java')) {
            throw new \RuntimeException('Could not find Java', 1445096476);
        }
    }

    /**
     * Returns the Tika version.
     *
     * @return string
     */
    public function getTikaVersion()
    {
        $version = '';
        $tikaCommand = CommandUtility::getCommand('java')
            . ' -jar ' . CommandUtility::escapeShellArgument($this->getTikaJar())
            . ' --version';

        $shellOutput = array();
        CommandUtility::exec($tikaCommand, $shellOutput);
        $version = $shellOutput[0];

        return $version;
    }

    /**
     * Returns a list of supported file types.
     *
     * @return array
     */
    public function getSupportedFileExtensions()
    {
        $tikaCommand = CommandUtility::getCommand('java')
            . ' -jar ' . CommandUtility::escapeShellArgument($this->getTikaJar())
            . ' --list-supported-types';

        $shellOutput = array();
        CommandUtility::exec($tikaCommand, $shellOutput);

        $fileTypes = array();
        foreach ($shellOutput as $mimeType) {
            if ($mimeType{0} === ' ') {
                continue;
            }
            $extensions = \Causal\Extractor\Utility\MimeType::getFileExtensions($mimeType);
            if (!empty($extensions)) {
                $fileTypes = array_merge($fileTypes, $extensions);
            }
        }

        $fileTypes = array_unique($fileTypes);

        return $fileTypes;
    }

    /**
     * Returns Java runtime information.
     *
     * @return array
     */
    public function getJavaRuntimeInfo()
    {
        $cmd = CommandUtility::getCommand('java');
        $info = [
            'path' => $cmd,
        ];

        $shellOutput = [];
        CommandUtility::exec($cmd . ' -version 2>&1', $shellOutput);
        if (!empty($shellOutput)) {
            if (preg_match('/^.*"(.+)"/', $shellOutput[0], $matches)) {
                $info['version'] = $matches[1];
            }
            if (!empty($shellOutput[1])) {
                $info['description'] = $shellOutput[1];
            }
        }

        return $info;
    }

    /**
     * Takes a file reference and extracts its metadata.
     *
     * @param \TYPO3\CMS\Core\Resource\File $fileName
     * @return array
     */
    public function extractMetadataFromLocalFile($fileName)
    {
        $tikaCommand = CommandUtility::getCommand('java')
            . ' -Dfile.encoding=UTF8'
            . ' -jar ' . CommandUtility::escapeShellArgument($this->getTikaJar())
            . ' -m --json'
            . ' ' . CommandUtility::escapeShellArgument($fileName);

        $shellOutput = [];
        CommandUtility::exec($tikaCommand, $shellOutput);

        static::getLogger()->debug(
            'Executing external script',
            [
                'commmand' => $tikaCommand,
                'output' => $shellOutput,
            ]
        );

        $metadata = json_decode($shellOutput[0], true);

        return $metadata;
    }

    /**
     * Takes a file reference and detects its content's language.
     *
     * @param \TYPO3\CMS\Core\Resource\File $file
     * @return string Language ISO code
     */
    public function detectLanguage(File $file)
    {
        $localTempFilePath = $file->getForLocalProcessing(false);
        $language = $this->detectLanguageFromLocalFile($$localTempFilePath);
        $this->cleanupTempFile($localTempFilePath, $file);

        return $language;
    }

    /**
     * Takes a file reference and detects its content's language.
     *
     * @param string $fileName Path to the file
     * @return string
     */
    public function detectLanguageFromLocalFile($fileName)
    {
        $tikaCommand = CommandUtility::getCommand('java')
            . ' -Dfile.encoding=UTF8'
            . ' -jar ' . CommandUtility::escapeShellArgument($this->getTikaJar())
            . ' -l'
            . ' ' . CommandUtility::escapeShellArgument($fileName);

        return trim(CommandUtility::exec($tikaCommand));
    }

    /**
     * Returns the path to the Apache Tika JAR.
     *
     * @return string
     */
    protected function getTikaJar(): string
    {
        return is_file($this->settings['tika_jar_path'])
            ? $this->settings['tika_jar_path']
            : GeneralUtility::getFileAbsFileName($this->settings['tika_jar_path']);
    }
}
