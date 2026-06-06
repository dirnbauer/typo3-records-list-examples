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

.. _introduction-templates:

Template architecture
=====================

Timeline and Catalog use custom Fluid templates, but shared backend chrome is
extracted into extension-local partials under
:file:`Resources/Private/Backend/Partials/`:

* :file:`RecordListTables.html` -- shared table shell with filters, form, and pagination
* :file:`CatalogRecordList.html` / :file:`CatalogRecordCard.html` -- catalog record layout
* :file:`TimelineRecordList.html` / :file:`TimelineRecordItem.html` -- timeline record layout
* :file:`TableHeadingBlock.html` -- table heading and multi-record-selection bar
* :file:`RecordTitleRow.html` / :file:`RecordTeaser.html` / :file:`FirstDisplayValue.html` -- shared record header and display-value helpers
* :file:`RecordActions.html` -- edit, visibility, delete, and more-actions menu
* :file:`TranslationStrip.html` -- per-language translation slots
* :file:`MultiRecordCheckbox.html`, :file:`ExpandTableLink.html`,
  :file:`NoRecordsCallout.html` -- small reusable UI fragments

Both custom views set ``partialRootPath`` in Page TSconfig. EXT:records_list_types
prepends that path and still resolves parent partials such as
``TableHeading``, ``RecordFilters``, and ``Pagination`` from the main extension.

View templates under :file:`Resources/Private/Backend/Templates/` are layout-only
entry points. Each renders :file:`RecordListTables.html` with a view-specific
record-list partial:

* Catalog -- :file:`CatalogRecordList.html` with image cards, placeholders, and preview hints
* Timeline -- :file:`TimelineRecordList.html` with date column, connecting line, and content cards

Shared markup for actions, checkboxes, and translations uses the
``rle-record-card`` BEM block. Matching styles live in
:file:`Resources/Public/Css/record-card-shared.css`, which both view stylesheets
import. Dark-mode overrides for shared warning and danger tokens apply to both
catalog and timeline containers.

:file:`RecordTeaser.html` and :file:`FirstDisplayValue.html` render only the
first matching ``displayValues`` entry. This keeps catalog teasers and timeline
date circles single-purpose without scanning logic duplicated in each view.

The other four example views reuse built-in templates from EXT:records_list_types
and need only TSconfig configuration.
