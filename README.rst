Bridge between Metadata Services and FAL Extractors
===================================================

This extension provides a bridge between TYPO3 metadata extraction services and FAL
extractors.

In addition to this extension, you need other extensions providing metadata extraction services such as "svmetaextract"
or "tika".

At the moment, "svmetaextract" and "tika" are known to work properly.

.. tip::
	Despite their name and/or description, "svmetaextract" does not require DAM and "tika" does not
	require "Apache Solr".


svmetaextract
-------------

This extension is capable of extracting IPTC and EXIF with native PHP data and takes advantage of third optional external
binaries :program:`exiftools`, :program:`exiftags` and :program:`pdfinfo` for better extraction and support for XMP
metadata extension.

You may check that the extraction service is properly configured by opening module System > Reports > Installed Services
and check section "Service type: metaExtract". Every service you want to work should be "green".

Additional information: http://typo3.org/extensions/repository/view/svmetaextract


Implemented
^^^^^^^^^^^

- tx_svmetaextract_sv1 (IPTC metadata with PHP solely)
- tx_svmetaextract_sv2 (EXIF metadata with PHP solely)
- tx_svmetaextract_sv3 (EXIF metadata using external tool :program:`exiftags`)
- tx_svmetaextract_sv4 (EXIF/IPTC/XMP metadata using external tool :program:`exiftools`)
- tx_svmetaextract_sv5 (PDF metadata using external tool :program:`pdfinfo`)
- tx_svmetaextract_sv6 (XMP metadata with PHP solely)


Apache Tika
-----------

This extension is capable of extracting loads of metadata and guess the language of documents from many many file
formats. It requires Java in order to be able to run a JAR archive you have to manually download and place wherever you
want on your server.

Make sure to "Clear all caches" when installing this extension as the activation of the various tika services is
triggered by this TYPO3 mechanism.

Additional information: http://typo3.org/extensions/repository/view/tika


Implemented
^^^^^^^^^^^

- metadata extraction for image files
- metadata extraction for common document types


How to help
-----------

Test and provide JSON mapping configuration files for other metadata extraction services.

Thanks!
