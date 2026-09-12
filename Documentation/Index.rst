.. _start:

=====================
Records List Examples
=====================

:Extension key:
    records_list_examples

:Package name:
    webconsulting/records-list-examples

:Version:
    1.3.0

:TYPO3:
    14.3 or later

:PHP:
    8.4 or 8.5

This extension adds six ready-to-use view types for the TYPO3 backend Records
module provided by EXT:records_list_types: Timeline, Catalog, Address book,
Event list, Gallery and Dashboard. It is TYPO3 v14 only and intentionally
contains no PHP runtime classes.

..  toctree::
    :maxdepth: 2
    :titlesonly:

    Introduction/Index
    Installation/Index
    ExampleViews/Index
    Configuration/Index
    Developer/Index

.. _scope:

Scope
=====

The package demonstrates EXT:records_list_types configuration through TYPO3
Page TSconfig, Fluid templates, CSS and XLIFF labels. Timeline and Catalog ship
custom Fluid templates with shared partials and CSS; the other four views reuse
the templates of EXT:records_list_types and need only TSconfig.

Use it as it is to give editors more ways to look at records, or as a starting
point for view types of your own.
