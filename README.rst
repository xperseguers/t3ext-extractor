TYPO3 Bridge Metadata Extractor for FAL
=======================================

This extension provides a bridge between TYPO3 metadata extraction services and FAL
extractors.

In addition to this extension, you need other extensions providing metadata extraction services such as "svmetaextract"
or "tika".

At the moment, "svmetaextract" is known to work properly (and does not require DAM despite its name).


svmetaextract
-------------

This extension is capable of extracting IPTC and EXIF with native PHP data and takes advantage of two optional external
binaries :program:`exiftools` and :program:`exiftags`.

Additional information: http://typo3.org/extensions/repository/view/svmetaextract
