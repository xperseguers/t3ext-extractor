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

namespace Causal\Extractor\Resource\Event;

use TYPO3\CMS\Core\Resource\FileInterface;

/**
 * This event is fired after the metadata of a file have been extracted.
 *
 * Use case: Using listeners for this event allows to e.g., extend the
 * metadata with specific information based on your business model.
 */
final class AfterMetadataExtractedEvent
{

    /**
     * @var FileInterface
     */
    private $file;

    /**
     * @var array
     */
    private $metadata;

    /**
     * AfterMetadataExtractedEvent constructor.
     *
     * @param FileInterface $file
     * @param array $metadata
     */
    public function __construct(FileInterface $file, array $metadata)
    {
        $this->file = $file;
        $this->metadata = $metadata;
    }

    /**
     * @return FileInterface
     */
    public function getFile(): FileInterface
    {
        return $this->file;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

}