.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _introduction:

Introduction
============


.. _what-it-does:

What does it do?
----------------

This extension detects and extracts metadata (EXIF / IPTC / XMP / ...) from potentially thousand different file types
(such as MS Word/Powerpoint/Excel documents, PDF and images) and bring them automatically and natively to TYPO3 when
uploading assets.

It works with built-in PHP functions but takes advantage of Apache Tika and other external tools for enhanced metadata
extraction.

.. image:: ../Images/camera-data.png
    :alt: Metadata for an image


.. _requirements:

Requirements
------------

- PHP methods: ``exif_read_data``, ``iptcparse``

Following tools are *optional* but **recommenced** for best extraction results:

Apache Tika
    The Apache Tika\ |TM| toolkit detects and extracts metadata and text from over a thousand different file types (such
    as PPT, XLS, and PDF). All of these file types can be parsed through a single interface, making Tika useful for
    search engine indexing, content analysis, translation, and much more.

    Use of PHP method ``fsockopen`` is required when using Tika Server.

ExifTool
    ExifTool is a plateform-independant Perl library plus a command-line application for reading, writing and editing
    meta information in a wide variety of files. ExifTool supports many different metadata formats including EXIF, GPS,
    IPTC, XMP, JFIF, GeoTIFF, ICC Profile, Photoshop IRB, FlashPix, AFCP and ID3, as well as the maker notes of many
    digital cameras.

    ExifTool is also available as a standalone Windows executable (which does not require Perl) and a Macintosh OS X
    package.

Pdfinfo
    Pdfinfo prints the content of the "Info" dictonary (plus some other useful information) from a Portable Document
    Format (PDF) file.
