.. _start:

=====================
Records List Examples
=====================

:Extension key:
    records_list_examples

:Package name:
    webconsulting/records-list-examples

:Version:
    1.2.1

:TYPO3:
    14.3 or later

:PHP:
    8.3 through 8.5

This extension adds example view types for the TYPO3 backend Records module
provided by EXT:records_list_types. It is TYPO3 v14 only and intentionally
contains no PHP runtime classes.

..  toctree::
    :maxdepth: 2
    :titlesonly:

    Introduction/Index
    Installation/Index
    Configuration/Index

.. _scope:

Scope
=====

The package demonstrates Records List Types configuration through TYPO3 Page
TSconfig, Fluid templates, CSS, and XLIFF labels:

* Timeline
* Catalog
* Address Book
* Event List
* Gallery
* Dashboard

Timeline and Catalog ship custom Fluid templates with shared partials and CSS.
The other views reuse the templates from EXT:records_list_types.
