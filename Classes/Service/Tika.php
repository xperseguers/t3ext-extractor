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

use TYPO3\CMS\Core\Utility\CommandUtility;

/**
 * Apache Tika client.
 *
 * @category    Service
 * @package     TYPO3
 * @subpackage  tx_extractor
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Tika
{

    /**
     * @var string
     */
    protected $applicationPath;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * Tika constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
       switch ($parameters['tika_mode']) {
           case 'jar':
               $this->applicationPath = $parameters['tika_jar_path'];
               break;
           case 'server':
               $this->host = $parameters['tika_server_host'];
               $this->port = (int)$parameters['tika_server_port'];
               break;
       }
    }

    /**
     * Returns true if this service is available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        $applicationCommand = $this->getApplicationCommand();
        return true;
    }

    /**
     * Returns the Tika version.
     *
     * @return string
     */
    public function getTikaVersion()
    {
        $version = '';
        if (!empty($this->applicationPath) && file_exists($this->applicationPath)) {
            $cmd = CommandUtility::getCommand('java');
            if ($cmd !== false) {
                $output = array();
                CommandUtility::exec($cmd . ' -jar ' . escapeshellarg($this->applicationPath) . ' --version', $output);
                $version = $output[0];
            }
        }
        return $version;
    }

    /**
     * Returns Java runtime information.
     *
     * @return array
     */
    public function getJavaRuntimeInfo()
    {
        $info = array();
        $cmd = CommandUtility::getCommand('java');
        if ($cmd !== false) {
            $info['path'] = $cmd;
            $output = array();
            CommandUtility::exec($cmd . ' -version 2>&1', $output);
            if (!empty($output)) {
                if (preg_match('/^.*"(.+)"/', $output[0], $matches)) {
                    $info['version'] = $matches[1];
                }
                if (!empty($output[1])) {
                    $info['description'] = $output[1];
                }
            }
        }
        return $info;
    }

}
