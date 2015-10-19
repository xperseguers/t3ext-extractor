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
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A Tika service implementation using the tika-server-x.x.jar.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ServerService extends AbstractTikaService
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
    public function getSupportedFileTypes()
    {
        throw new \RuntimeException('Not yet implemented');
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
     * Sends a command to the Tika server.
     *
     * @param string $method HTTP method ("GET", "POST", ...)
     * @param string $resource
     * @return string
     */
    protected function send($method, $resource)
    {
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
        $out .= 'Connection: Close' . CRLF . CRLF;
        fwrite($fh, $out);
        $buffer = '';
        while (!feof($fh)) {
            $buffer .= fgets($fh, 1024);
        }
        fclose($fh);

        list($header, $payload) = explode(CRLF . CRLF, $buffer);

        return $payload;
    }

    /**
     * Takes a file reference and extracts the text from it.
     *
     * @param \TYPO3\CMS\Core\Resource\File $file
     * @return string
     */
    public function extractText(File $file)
    {
        // TODO: Implement extractText() method.
    }

    /**
     * Takes a file reference and extracts its metadata.
     *
     * @param \TYPO3\CMS\Core\Resource\File $file
     * @return array
     */
    public function extractMetaData(File $file)
    {
        // TODO: Implement extractMetaData() method.
    }

    /**
     * Takes a file reference and detects its content's language.
     *
     * @param \TYPO3\CMS\Core\Resource\File $file
     * @return string Language ISO code
     */
    public function detectLanguageFromFile(File $file)
    {
        // TODO: Implement detectLanguageFromFile() method.
    }

    /**
     * Takes a string as input and detects its language.
     *
     * @param string $input
     * @return string Language ISO code
     */
    public function detectLanguageFromString($input)
    {
        // TODO: Implement detectLanguageFromString() method.
    }

}