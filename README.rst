Bridge between Metadata Services and FAL Extractors
===================================================

This extension provides a bridge between TYPO3 metadata extraction services and FAL
extractors.

In addition to this extension, you need other extensions providing metadata extraction services such as "svmetaextract"
or "tika".

At the moment, "svmetaextract" is known to work properly (and does not require DAM despite its name).


svmetaextract
-------------

This extension is capable of extracting IPTC and EXIF with native PHP data and takes advantage of third optional external
binaries :program:`exiftools`, :program:`exiftags` and :program:`pdfinfo` for better extraction and support for XMP
metadata extension.

You may check that the extraction service is properly configured by opening module System > Reports > Installed Services
and check section "Service type: metaExtract". Everything service you want to work should be "green".

Additional information: http://typo3.org/extensions/repository/view/svmetaextract


Implemented
^^^^^^^^^^^

- tx_svmetaextract_sv1 (IPTC metadata with PHP solely)
- tx_svmetaextract_sv2 (EXIF metadata with PHP solely)
- tx_svmetaextract_sv3 (EXIF metadata using external tool :program:`exiftags`)
- tx_svmetaextract_sv4 (EXIF/IPTC/XMP metadata using external tool :program:`exiftools`)
- tx_svmetaextract_sv5 (PDF metadata using external tool :program:`pdfinfo`)


How to help
-----------

Test and provide JSON mapping configuration files for other metadata extraction services.

Thanks!
