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

namespace Causal\Extractor\Em;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Causal\Extractor\Service;
use Causal\Extractor\Utility\SimpleString;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * AJAX controller for Extension Manager.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class AjaxController
{
    /**
     * Renders the menu so that it can be returned as response to an AJAX call
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function analyze(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = func_num_args() === 2 ? func_get_arg(1) : null;

        $success = false;
        $html = '';
        $preview = '';
        $mappingFileNames = [];

        if ($GLOBALS['BE_USER']->isAdmin()) {
            $queryParameters = $request->getQueryParams();
            $file = $queryParameters['file'];
            $service = $queryParameters['service'];

            $publicUrl = '';
            $file = $this->getFile($file, $publicUrl);

            /** @var Service\ServiceInterface $extractor */
            $extractor = null;

            /** @var Service\Extraction\AbstractExtractionService $extractionService */
            $extractionService = null;

            try {
                switch ($service) {
                    case 'exiftool':
                        $extractor = GeneralUtility::makeInstance(Service\ExifTool\ExifToolService::class);
                        $extractionService = GeneralUtility::makeInstance(Service\Extraction\ExifToolMetadataExtraction::class);
                        break;
                    case 'pdfinfo':
                        $extractor = GeneralUtility::makeInstance(Service\Pdfinfo\PdfInfoService::class);
                        $extractionService = GeneralUtility::makeInstance(Service\Extraction\PdfinfoMetadataExtraction::class);
                        break;
                    case 'php':
                        $extractor = GeneralUtility::makeInstance(Service\Php\PhpService::class);
                        $extractionService = GeneralUtility::makeInstance(Service\Extraction\PhpMetadataExtraction::class);
                        break;
                    case 'tika':
                        $extractor = Service\Tika\TikaServiceFactory::getTika();
                        $extractionService = GeneralUtility::makeInstance(Service\Extraction\TikaMetadataExtraction::class);
                        break;
                }
            } catch (\Exception $e) {
                $html = $e->getMessage();
            }

            if ($extractor !== null) {
                $success = true;
                $metadata = $extractor->extractMetadata($file);
                $html = $this->htmlizeMetadata($metadata);

                // Populate the possible mapping configuration file names
                $mappingFileNames = $extractionService->getPotentialMappingFiles($file);
                $typo3Branch = class_exists(\TYPO3\CMS\Core\Information\Typo3Version::class)
                    ? (new \TYPO3\CMS\Core\Information\Typo3Version())->getBranch()
                    : TYPO3_branch;
                if (version_compare($typo3Branch, '9.0', '<')) {
                    $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['extractor'] ?? '') ?? [];
                } else {
                    $settings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('extractor') ?? [];
                }
                if (isset($settings['mapping_base_directory'])) {
                    $pathConfiguration = is_dir($this->settings['mapping_base_directory'])
                        ? $this->settings['mapping_base_directory']
                        : GeneralUtility::getFileAbsFileName($this->settings['mapping_base_directory']);
                    foreach ($mappingFileNames as &$fileName) {
                        $fileName = substr($fileName, strlen($pathConfiguration));
                    }
                }

                if (in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'gif'])) {
                    $preview = '<img src="' . $publicUrl . '" alt="" width="300" class="img-responsive" />';
                }
            }
        }

        $out = [
            'success' => $success,
            'preview' => $preview,
            'html' => $html,
            'files' => $mappingFileNames,
        ];

        if ($response !== null) {
            $response->getBody()->write(json_encode($out));
        } else {
            // Behaviour in TYPO3 v9
            $response = new JsonResponse($out);
        }

        return $response;
    }

    /**
     * Processes a sample value.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = func_num_args() === 2 ? func_get_arg(1) : null;
        $text = '';

        if ($GLOBALS['BE_USER']->isAdmin()) {
            $queryParameters = $request->getQueryParams();
            $sample = $queryParameters['sample'];
            $processor = $queryParameters['processor'];

            if (preg_match('/^([^(]+)(\((.*)\))?$/', $processor, $matches)) {
                $processor = $matches[1];
                if (preg_match('/[[].*[]]/', $sample)) {
                    $value = json_decode($sample, true);
                    if ($value === null) {
                        // Probably not a JSON string after all
                        $value = $sample;
                    }
                    $sample = $value;
                }
                $parameters = [$sample];
                // Support for $matches[3] is currently *very* basic!
                // @see \Causal\Extractor\Service\Extraction\AbstractExtractionService::remapServiceOutput
                if ($matches[3]) {
                    if (substr($matches[3], 0, 1) === '\'') {
                        $parameters[] = substr($matches[3], 1, -1);
                    } else {
                        $parameters[] = $matches[3];
                    }
                }
                try {
                    $text = call_user_func_array($processor, $parameters);
                } catch (\Exception $e) {
                    $text = sprintf('Exception #%s: %s', $e->getCode(), $e->getMessage());
                }
            }
        }

        $out = [
            'success' => true,
            'text' => $text,
        ];

        if ($response !== null) {
            $response->getBody()->write(json_encode($out));
        } else {
            // Behaviour in TYPO3 v9
            $response = new JsonResponse($out);
        }

        return $response;
    }

    /**
     * Returns a FAL file.
     *
     * @param string $reference
     * @param string &$publicUrl
     * @return FileInterface|null
     */
    protected function getFile($reference, &$publicUrl)
    {
        $file = null;
        $extensionPrefix = 'EXT:extractor/Resources/Public/';

        /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resourceFactory */
        $resourceFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);

        if (SimpleString::isFirstPartOfStr($reference, $extensionPrefix)) {
            $fileName = substr($reference, strlen($extensionPrefix));
            $recordData = [
                'uid' => 0,
                'pid' => 0,
                'name' => 'Resource Extension Storage',
                'description' => 'Internal storage, mounting the extension Resources/Public directory.',
                'driver' => 'Local',
                'processingfolder' => '',
                // legacy code
                'configuration' => '',
                'is_online' => true,
                'is_browsable' => false,
                'is_public' => false,
                'is_writable' => false,
                'is_default' => false,
            ];
            $storageConfiguration = [
                'basePath' => GeneralUtility::getFileAbsFileName($extensionPrefix),
                'pathType' => 'absolute'
            ];

            $virtualStorage = $resourceFactory->createStorageObject($recordData, $storageConfiguration);
            $name = PathUtility::basename($fileName);
            $extension = strtolower(substr($name, strrpos($name, '.') + 1));

            /** @var \TYPO3\CMS\Core\Resource\File $file */
            $file = GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Resource\File::class,
                [
                    'identifier' => '/' . $fileName,
                    'name' => $name,
                    'extension' => $extension,
                ],
                $virtualStorage,
                [
                    // Trick to let FAL thinks the file is indexed
                    '_' => 1,
                ]
            );

            $identifier = rtrim($storageConfiguration['basePath'], '/') . $file->getIdentifier();
            $publicUrl = PathUtility::getAbsoluteWebPath($identifier);
        } elseif (preg_match('/^file:(\d+):(.*)$/', $reference, $matches)) {
            $storage = $resourceFactory->getStorageObject((int)$matches[1]);
            $file = $storage->getFile($matches[2]);
            $publicUrl = $file->getPublicUrl(true);
        }

        return $file;
    }

    /**
     * HTML-izes an array of metadata.
     *
     * @param array $metadata
     * @param int $indent
     * @param null|string $parent
     * @return mixed
     */
    protected function htmlizeMetadata(array $metadata, int $indent = 0, ?string $parent = null)
    {
        $html = [];

        $html[] = 'array(';

        foreach ($metadata as $key => $value) {
            $keyName = ($parent ? $parent . '|' : '') . $key;
            $processor = $this->suggestProcessor($value, $key);
            $propertyPath = $keyName . ($processor ? '->' . $processor : '');
            $sample = is_array($value) ? json_encode(array_values($value)) : htmlspecialchars($value);

            $property = '\'<a class="tx-extractor-property" href="#"' .
                ' data-property="' . htmlspecialchars($keyName) . '"' .
                ' data-processor="' . htmlspecialchars($processor) . '"' .
                ' data-sample=\'' . $sample . '\'>' . htmlspecialchars($key) . '</a>\'';

            if (is_array($value)) {
                $value = $this->htmlizeMetadata($value, $indent + 1, $keyName);
            } else {
                $value = '\'' . htmlspecialchars(str_replace('\'', '\\\'', $value)) . '\'';
            }

            $html[] = str_repeat('  ', $indent + 1) . $property . ' => ' . $value . ',';
        }

        $html[] = str_repeat('  ', $indent) . ')';

        return implode(PHP_EOL, $html);
    }

    /**
     * Suggests a processor to be used for extracting a given value.
     *
     * @param mixed $value
     * @param string $property
     * @return string
     */
    protected function suggestProcessor($value, string $property)
    {
        $postProcessor = null;

        switch (true) {
            case stripos($property, 'date') !== false:
            case stripos($property, 'modified') !== false:
            case stripos($property, 'created') !== false:
                $postProcessor = \Causal\Extractor\Utility\DateTime::class . '::timestamp';
                break;
            case stripos($property, 'gps') !== false:
                $postProcessor = \Causal\Extractor\Utility\Gps::class . '::toDecimal';
                break;
            case is_array($value):
                $postProcessor = \Causal\Extractor\Utility\Array_::class . '::concatenate(\', \')';
                break;
        }

        return $postProcessor;
    }
}
