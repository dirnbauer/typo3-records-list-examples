.. _introduction:

============
Introduction
============

EXT:records_list_examples is an examples package for TYPO3 v14 installations
that use EXT:records_list_types.

The extension registers six backend Records module view types through TYPO3's
automatic :file:`Configuration/page.tsconfig` inclusion. No
:php:`ExtensionManagementUtility::addPageTSConfig()` call is needed or used.

The six views are described in :ref:`example-views`; how they are configured is
described in :ref:`configuration`.

.. _introduction-api-first:

API-first approach
==================

The custom templates use the existing TYPO3 and EXT:records_list_types APIs:

* TYPO3 backend contextual edit triggers for record editing
* TYPO3 Fluid sanitization for backend-generated fragments
* EXT:records_list_types permission-aware action data
* EXT:records_list_types shared JavaScript hooks
* TYPO3 translation domains for localization

The extension adds no PHP controllers, services, middleware or database access.
Everything it contributes is Page TSconfig, Fluid templates, CSS and XLIFF
labels, which is exactly what a custom view type needs.

.. _introduction-audience:

Who this is for
===============

*   **Editors** get six additional ways to look at records without any
    development work.
*   **Integrators** get working TSconfig for both kinds of view type: one that
    only configures a built-in template, and one that ships its own.
*   **Developers** get a reference implementation of the template contract of
    EXT:records_list_types, see :ref:`developer`.
