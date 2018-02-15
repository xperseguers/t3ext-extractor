.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developer-manual:

Developer manual
================

.. only:: html

	This chapter describes some internals of this extension to let you extend it easily.


Assets such as PDF, images, documents, ... are uploaded to TYPO3. Metadata extraction services are called, one after
another, based on their advertised priority or quality. These services are the various extraction classes you find under
:file:`Classes/Service/Extraction/`).

The service classes invoke the actual wrappers to the extraction tools (Apache Tika, ExifTool, PHP, ...) to be found
under :file:`Classes/Service/{Wrapper}/`.

In order to map the data format used by the various extraction tools to the FAL metadata structure used by TYPO3, a
JSON-based configuration file is used. Those mapping configuration files can be found under
:file:`Configuration/Services/{Wrapper}/`.

.. figure:: ../Images/workflow.png
    :alt: Overview of the extraction of metadata in TYPO3

    Overview of the workflow of metadata extraction in TYPO3 when using this extension.


.. _developer-manual-json:

JSON mapping configuration file
-------------------------------

A mapping configuration file is of the form:

.. code-block:: json

    [
      {
        "FAL": "caption",
        "DATA": "CaptionAbstract"
      },
      {
        "FAL": "color_space",
        "DATA": [
          "ColorMode",
          "ColorSpaceData",
          "ColorSpace->Causal\\Extractor\\Utility\\ColorSpace::normalize"
        ]
      }
    ]

FAL
    This is the name (column) of the metadata in FAL.

DATA
    This is either a unique key or an array of ordered keys to be checked for content in the extracted metadata. In
    addition, an arbitrary post-processor may be specified using the ``->`` array notation.

.. figure:: ../Images/configuration-helper-tool.png
    :alt: Configuration Helper Tool

    A configuration helper tool is available in Extension Manager.


.. _developer-manual-hook:

Hook
----

The method ``\Causal\Extractor\Service\Extraction\AbstractExtractionService::getDataMapping()`` is the central method
invoked to map extracted metadata to FAL properties. Developers may dynamically alter the mapping by hooking into the
process using ``$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extractor']['dataMappingHook']``.


.. _developer-manual-signal:

Signal after extraction
-----------------------

Once the meta data has been extracted, a signal is emitted, which allows other extensions to process the file further.
The Signal can be connected to a Slot as follows (e.g. in file ``ext_localconf.php`` of your extension).

.. code-block:: php

    // Initiate SignalSlotDispatcher
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        'TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher'
    );

    // Connect the Signal "postMetaDataExtraction" to a Slot
    $signalSlotDispatcher->connect(
        'Causal\\Extractor\\Service\\AbstractService',
        'postMetaDataExtraction',
        'Vendor\\MyExtension\\Service\\SlotService',
        'dispatch'
    );

This requires a PHP class ``\Vendor\MyExtension\Service\SlotService`` and a method ``dispatch()`` in this class.
The FAL ``$storageRecord`` is passed as a parameter to the method.
