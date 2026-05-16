.. _introduction:

============
Introduction
============

EXT:records_list_examples is an examples package for TYPO3 v14 installations
that use EXT:records_list_types.

The extension registers six backend Records module view types. The registration
is done through TYPO3's automatic :file:`Configuration/page.tsconfig`
inclusion. No :php:`ExtensionManagementUtility::addPageTSConfig()` call is
needed or used.

.. _introduction-api-first:

API-first approach
==================

The custom templates use the existing TYPO3 and Records List Types APIs:

* TYPO3 backend contextual edit triggers for record editing
* TYPO3 Fluid sanitization for backend-generated fragments
* Records List Types permission-aware action data
* Records List Types shared JavaScript hooks
* TYPO3 XLIFF labels for localization

The extension does not add custom PHP controllers, services, middleware, or
database access.
