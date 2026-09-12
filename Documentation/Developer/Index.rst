.. _developer:

=========
Developer
=========

The extension contains no PHP runtime classes. It registers view types through
Page TSconfig and ships Fluid templates, CSS and XLIFF labels.

.. _developer-structure:

Repository layout
=================

..  code-block:: text
    :caption: What lives where

    Configuration/page.tsconfig                     Auto-loaded entry point
    Configuration/TsConfig/Page/setup.tsconfig      All six view registrations
    Resources/Private/Backend/Templates/            Layout-only view templates
    Resources/Private/Backend/Partials/             Shared record chrome
    Resources/Private/Language/                     XLIFF 2.0 labels (en, de)
    Resources/Public/Css/                           Shared and per-view styles
    Tests/Unit/                                     TSconfig, labels, templates
    Tests/Functional/                               Rendering of all six views

.. _developer-templates:

Template architecture
=====================

The two view templates under :file:`Resources/Private/Backend/Templates/` are
layout-only entry points. Each renders :file:`RecordListTables.html` with a
view-specific record-list partial:

*   Catalog renders :file:`CatalogRecordList.html` and
    :file:`CatalogRecordCard.html`
*   Timeline renders :file:`TimelineRecordList.html` and
    :file:`TimelineRecordItem.html`

Everything shared lives in :file:`Resources/Private/Backend/Partials/`:
:file:`RecordListTables.html` (filters, form, pagination),
:file:`TableHeadingBlock.html`, :file:`RecordTitleRow.html`,
:file:`RecordTeaser.html`, :file:`FirstDisplayValue.html`,
:file:`RecordActions.html`, :file:`TranslationStrip.html`,
:file:`MultiRecordCheckbox.html`, :file:`ExpandTableLink.html` and
:file:`NoRecordsCallout.html`.

Both custom views set ``partialRootPath``. EXT:records_list_types appends that
path after its own, so a partial of the same name wins here while parent
partials such as ``TableHeading``, ``RecordFilters`` and ``Pagination`` still
resolve from EXT:records_list_types.

Shared markup uses the ``rle-record-card`` BEM block; the matching styles live
in :file:`record-card-shared.css`, which both view stylesheets import.

.. _developer-contract:

Template contract
=================

Custom templates keep working across EXT:records_list_types releases as long as
they follow its systematic. The unit suite enforces these rules:

*   TYPO3-generated fragments (action buttons, multi-record-selection actions)
    go through ``f:sanitize.html(build: 'records-list-types-backend-fragments')``
    instead of ``f:format.raw``
*   record editing uses ``typo3-backend-contextual-record-edit-trigger``
*   labels use translation domains, never ``LLL:`` paths or ``default``
    attributes that duplicate the text
*   visibility toggles carry state-aware accessible names
    (``action.hide`` / ``action.unhide``), and icon-only actions have an
    ``aria-label``
*   backend templates never use frontend page ViewHelpers such as
    ``f:render.contentArea``

.. _developer-add-view:

Adding another view
===================

#.  Register the view type in
    :file:`Configuration/TsConfig/Page/setup.tsconfig` with ``template``,
    ``templateRootPath``, ``partialRootPath`` and ``css``.
#.  Add a record-list partial, for example :file:`KanbanRecordList.html`, with
    the layout that genuinely differs.
#.  Add a layout-only template that renders :file:`RecordListTables.html` with
    your ``recordListPartial``.
#.  Reuse :file:`RecordTitleRow.html`, :file:`RecordTeaser.html`,
    :file:`RecordActions.html` and :file:`TranslationStrip.html`.
#.  Add view-specific CSS and ``@import`` :file:`record-card-shared.css`.
#.  Add the view to the data providers in
    :file:`Tests/Functional/View/ExampleViewRenderingTest.php` and
    :file:`Tests/Unit/Configuration/ViewTypePresetsTest.php`.

The reference for the payload a template receives is the
`custom view types manual
<https://github.com/dirnbauer/typo3-records-list-types/blob/main/Documentation/Developer/CustomViewTypes.rst>`__
of EXT:records_list_types.

This is a backend examples package. The templates deliberately do not render
frontend content areas, and frontend theme presets such as Bootstrap or
shadcn/ui belong in a sitepackage instead.

.. _developer-javascript:

JavaScript
==========

The extension ships no JavaScript. Record actions, visibility toggles,
pagination input handling and multi-record selection come from
EXT:records_list_types and TYPO3 Core, through the documented
``data-gridview-action`` attributes and TYPO3 backend web components.

A view that genuinely needs client-side state can load its own ES module with
the view type option ``js``.

.. _developer-tests:

Local development
=================

..  code-block:: bash
    :caption: Install and run the checks

    composer install
    Build/Scripts/runTests.sh -s ci          # composer, lint, cgl, phpstan, unit
    Build/Scripts/runTests.sh -s functional  # SQLite by default

The functional suite boots EXT:records_list_types together with this extension
and renders all six views. Export the ``typo3Database*`` variables to run it
against MariaDB instead of SQLite, as the CI workflow does.

PHPStan runs at level 8 with strict rules, and PHP-CS-Fixer uses the TYPO3
coding standards.
