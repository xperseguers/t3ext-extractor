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

use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Extension helper class.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ExtensionHelper
{

    /**
     * Returns the category of a given file extension.
     *
     * @param string $extension
     * @return string
     */
    public static function getExtensionCategory($extension)
    {
        switch ($extension) {
            case 'ai':
            case 'bmp':
            case 'draw':
            case 'gif':
            case 'ico':
            case 'jpeg':
            case 'jpg':
            case 'mng':
            case 'png':
            case 'psd':
            case 'tif':
            case 'tiff':
            case 'wbmp':
            case 'wmf':
            case 'xbm':
                return 'image';

            case 'doc':
            case 'docx':
            case 'pdf':
            case 'pps':
            case 'ppsx':
            case 'ppt':
            case 'pptx':
            case 'xls':
            case 'xlsx':
                return 'document';

            case 'bz2':
            case 'gz':
            case 'rar':
            case 'tar':
            case 'zip':
                return 'archive';

            case '3gp':
            case 'aac':
            case 'act':
            case 'aiff':
            case 'amr':
            case 'ape':
            case 'au':
            case 'awb':
            case 'dct':
            case 'dss':
            case 'dvf':
            case 'flac':
            case 'gsm':
            case 'm4a':
            case 'm4p':
            case 'mid':
            case 'mmf':
            case 'mp3':
            case 'mpc':
            case 'msv':
            case 'oga':
            case 'ogg':
            case 'ra':
            case 'rm':
            case 'sln':
            case 'tta':
            case 'vox':
            case 'wav':
            case 'wma':
            case 'wv':
                return 'audio';

            case '3g2':
            case 'avi':
            case 'f4a':
            case 'f4b':
            case 'f4p':
            case 'f4v':
            case 'flv':
            case 'm2v':
            case 'm4p':
            case 'm4v':
            case 'mkv':
            case 'mov':
            case 'mp2':
            case 'mp4':
            case 'mpe':
            case 'mpeg':
            case 'mpg':
            case 'mpv':
            case 'mxf':
            case 'nsv':
            case 'ogv':
            case 'qt':
            case 'vob':
            case 'webm':
            case 'wmv':
            case 'yuv':
                return 'video';
        }

        return 'other';
    }

}
