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
use Causal\Extractor\Utility\MimeType;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A Tika service implementation using the tika-server-x.x.jar.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ServerService extends AbstractService implements TikaServiceInterface
{
    /**
     * ServerService constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (empty($this->settings['tika_server_host'])) {
            throw new \RuntimeException(
                'Empty Tika host',
                1445098183
            );
        }
        if (empty($this->settings['tika_server_port'])) {
            // Assign the default Tika server port
            $this->settings['tika_server_port'] = 9998;
        }
    }

    /**
     * Returns the Tika version.
     *
     * @return string
     */
    public function getTikaVersion()
    {
        $tikaVersion = $this->send('GET', '/version');
        return $tikaVersion;
    }

    /**
     * Returns a list of supported file types.
     *
     * @return array
     */
    public function getSupportedFileExtensions()
    {
        $content = $this->send('GET', '/mime-types', 'application/json');
        $mimeTypes = json_decode($content, true);

        $fileTypes = array();
        foreach ($mimeTypes as $mimeType => $_) {
            $extensions = \Causal\Extractor\Utility\MimeType::getFileExtensions($mimeType);
            if (!empty($extensions)) {
                $fileTypes = array_merge($fileTypes, $extensions);
            }
        }

        $fileTypes = array_unique($fileTypes);
        return $fileTypes;
    }

    /**
     * Pings the Tika host.
     *
     * @return int -1 if host is down otherwise delay to connect to the Tika host
     */
    public function ping()
    {
        $starttime = microtime(true);
        $fh = @fsockopen(
            $this->settings['tika_server_host'],
            $this->settings['tika_server_port'],
            $errno,
            $errstr,
            5   // 5 seconds of timeout should be enough
        );
        $stoptime = microtime(true);
        $status = 0;

        if (!$fh) {
            // Host is down
            $status = -1;
        } else {
            fclose($fh);
            $status = ($stoptime - $starttime) * 1000;
            $status = floor($status);
        }
        return $status;
    }

    /**
     * Takes a file reference and extracts its metadata.
     *
     * @param string $file
     * @return array
     */
    public function extractMetadataFromLocalFile($fileName)
    {
        $content = $this->send('PUT', '/meta', 'application/json', $fileName);
        $metadata = json_decode($content, true);

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
        $language = $this->detectLanguageFromLocalFile($localTempFilePath);
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
        $language = $this->send('PUT', '/language/stream', '', $fileName);

        return $language;
    }

    /**
     * Sends a command to the Tika server.
     *
     * @param string $method HTTP method ("GET", "POST", ...)
     * @param string $resource
     * @param string $accept
     * @param string $fileName
     * @return string
     */
    protected function send($method, $resource, $accept = '', $fileName = '')
    {
        // Initiate the connection
        $fh = @fsockopen(
            $this->settings['tika_server_host'],
            $this->settings['tika_server_port'],
            $errno,
            $errstr,
            5   // 5 seconds of timeout should be enough
        );

        if (!$fh) {
            return null;
        }

        $out = strtoupper($method) . ' ' . $resource . ' HTTP/1.1' . CRLF;
        $out .= 'Host: ' . $this->settings['tika_server_host'] . CRLF;
        if (!empty($accept)) {
            $out .= 'Accept: ' . $accept . CRLF;
        }
        if (!empty($fileName)) {
            $extension = '';
            if (($pos = strrpos($fileName, '.')) !== false) {
                $extension = strtolower(substr($fileName, $pos + 1));
            }
            $out .= 'Content-Type: ' . ($extension ? MimeType::getMimeType($extension) : 'octet/stream') . CRLF;
            $out .= 'Content-Length: ' . filesize($fileName) . CRLF;
        }

        // Automatically close the connection afterwards
        $out .= 'Connection: Close' . CRLF . CRLF;

        if (!empty($fileName)) {
            $out .= file_get_contents($fileName);
        }

        // Send the request
        fwrite($fh, $out);

        // Read the response
        $buffer = '';
        while (!feof($fh)) {
            $buffer .= fgets($fh, 1024);
        }

        // Close the connection
        fclose($fh);

        list($header, $payload) = explode(CRLF . CRLF, $buffer);

        return $payload;
    }
}
