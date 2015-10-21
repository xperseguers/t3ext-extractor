.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _users-manual:

Users Manual
============

.. only:: html

    This chapter describes how to use the extension from a user point of view.


.. _users-manual-fields:

Available fields
----------------


.. table:: Overview of available fields in FAL

    =======================  ======================================  =======================================
    Field                    Title                                   Type
    =======================  ======================================  =======================================
    title                    Title                                   string
    width                    Width                                   integer
    height                   Height                                  integer
    `alternative`_           Alternative text or headline            string
    `description`_           Description                             string
    visible                  Visible                                 0 or 1
    status                   Status                                  - 1 (OK)
                                                                     - 2 (Pending)
                                                                     - 3 (Under review)
    `keywords`_              Keywords                                comma-separated list of strings
    caption                  Caption                                 string
    creator_tool             Creator tool                            string
    download_name            Download name                           string
    creator                  Creator                                 string
    publisher                Publisher                               string
    source                   Source                                  string
    `location_country`_      Country                                 string
    `location_region`_       Region                                  string
    `location_city`_         City                                    string
    latitude                 GPS latitude                            floating point
    longitude                GPS longitude                           floating point
    ranking                  Ranking / Rating                        integer (0-5)
    content_creation         Content creation date                   integer (timestamp)
    content_modification     Content modification date               integer (timestamp)
    note                     Note                                    string
    unit                     Unit (for width/height)                 - "px" - pixels
                                                                     - "mm" - millimeters
                                                                     - "cm" - centimeters
                                                                     - "m" - meters
                                                                     - "p" - pica (12 points, about 1/6 inch or 4.2 mm)
    duration                 Duration of the movie/sound             integer (number of seconds)
    color_space              Color space                             - "RGB"
                                                                     - "CMYK"
                                                                     - "CMY"
                                                                     - "YUV"
                                                                     - "grey"
                                                                     - "indx" (indexed)
    pages                    Number of pages                         integer
    language                 Language of the file                    string
    =======================  ======================================  =======================================


Field details
^^^^^^^^^^^^^

.. only:: html

	.. contents::
		:local:
		:depth: 2


Standard fields
"""""""""""""""

.. _users-manual-fields-alternative:

alternative
```````````

A headline is a brief publishable synopsis or summary of the contents of the photograph. Like a news story, the Headline
should grab attention, and telegraph the content of the image to the audience. Headlines need to be succinct. leave the
supporting narrative for the `Description <description>`_ field. Do not, however, confuse the Headline term with the
Title term.


.. _users-manual-fields-description:

description
```````````

The Description field, often referred to as a "caption" is used to describe the who, what (and possibly where and when)
and why of what is happening in the photograph. If there is a person or people in the image, this caption might include
their names, and/or their role in the action that is taking place. If the image is of a location, then it should give
information regarding the location. Don't forget to also include this same "geographical" information in the
`Geographical fields`_. The amount of detail you include will depend on the image and whether the image is documentary
or conceptual. Typically, editorial images come with complete caption text, while advertising images may not.


.. _users-manual-fields-keywords:

keywords
````````

Enter keywords (terms or phrases) used to express the subject of the content seen in the photograph. Keywords may be
free text (i.e. they are not required to be taken from a controlled vocabulary). You may enter any number of keywords,
terms or phrases into this field, simply separate them with a comma or semi-colon.


.. _users-manual-fields-geo:

Geographical fields
"""""""""""""""""""

According to the `IPTC standards <https://www.iptc.org/std/photometadata/documentation/IPTC-CS5-FileInfo-UserGuide_6.pdf>`__,
the descriptions of geographic fields contained within the IPTC Core Image section did not clearly distinguish wether
the value should be the actual location shown in the image, or the location where the photo was taken. Because most GPS
systems, by default, indicate where the photographer was standing, the IPTC standard is now suggesting to use the fields
City, Region and Country for the location "shown" in the image, whereas the latitude and longitude will logically be
related to the position the photographer was standing.


.. _users-manual-fields-location-country:

location_country
````````````````

Enter the full name of the country pictured in the photograph. This field is at the first level of a top-down
geographical hierarchy. The full name should be expressed as a verbal name and not as an ISO country code.



.. _users-manual-fields-location-region:

location_region
```````````````

Enter the name of the subregion of a country -- usually referred to as either a State or Province -- that is pictured in
the image. Since the abbreviation for a State or Province may be unknown to those viewing your metadata internationally,
consider using the full spelling of the name. Province/State is a the second level of a top-down geographical hierarchy.


.. _users-manual-fields-location-city:

location_city
`````````````

Enter the name of the city that is pictured in the image. If there is no city, consider using the name of the location
shown in the image. This name could be the name of a specific area within a city (Manhattan) or the name of a well-known
location (Pyramides of Giza) or (natural) monument outside a city (Grand Canyon). City is at the third level of a
top-down geographical hierarchy.
