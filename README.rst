Metadata and content analysis service
=====================================

This extension detects and extracts metadata and text (EXIF / IPTC / XMP / ...) from potentially thousand different file
types (such as MS Word/Powerpoint/Excel documents and PDF) and bring them automatically and natively to TYPO3 when
uploading assets.

.. image:: Documentation/Images/metadata.png
    :alt: Metadata for a document


Requirements
------------

For best results, `Apache Tika <https://tika.apache.org/download.html>`__ is required (either as standalone JAR or
running as server).

Extraction of metadata from common image files (jpg, tiff, ...) is often quicker using external tool
`exiv2 <http://www.exiv2.org/>`__ and if not available, it will fall back to PHP's built-in EXIF and IPTC library.
For PDF, external tool `pdfinfo <http://linuxcommand.org/man_pages/pdfinfo1.html>`__ will be used.


Read more in the `manual <https://docs.typo3.org/typo3cms/extensions/extractor/>`__.
