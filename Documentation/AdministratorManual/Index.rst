.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _admin-manual:

Administrator manual
====================

.. only:: html

	This chapter describes how to manage the extension from a superuser point of view.


.. _admin-manual-install:

Installing the extension
------------------------

There are a few steps necessary to install the Metadata and content analysis extension. If you have installed other
extensions in the past, you will run into little new here.

As usual, install the extension and load it using the Extension Manager. Then configure it by either clicking on the
gear icon or on the title of this extension.


Apache Tika
^^^^^^^^^^^

Using Apache Tika is highly recommended for best extraction results. You may use either the standalone application jar
or connect to an Apache Tika server. The latest should probably be quicker to answer since it run as a daemon.

Apache Tika may be downloaded from https://tika.apache.org/download.html.

**Connection to an Apache Tika Server**

When connecting to a server and not to the standalone Jar application, handy animations, in Extension Manager, will let
you easily double check that provided parameters are correct:

.. only:: html or json

    - .. image:: ../Images/animation_ok.gif
          :alt: Successful connection

    - .. image:: ../Images/animation_ko.gif
          :alt: Broken connection

.. only:: latex

    .. image:: ../Images/connection_success.png
        :alt: Successful connection

    .. image:: ../Images/connection_broken.png
        :alt: Broken connection


External Tools
^^^^^^^^^^^^^^

This extension is capable of using external tools to extract metadata:

- `exiv2 <http://www.exiv2.org/>`__ for image files;
- `pdfinfo <http://linuxcommand.org/man_pages/pdfinfo1.html>`__ for PDF.
