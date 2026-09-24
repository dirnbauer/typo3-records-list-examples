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

    Configuration/page.tsconfig             All six view registrations
    Resources/Private/Backend/Templates/    TimelineView, CatalogView
    Resources/Private/Backend/Partials/     Card partials and shared chrome
    Resources/Private/Language/             XLIFF 2.0 labels (en, de)
    Resources/Public/Css/                   Shared and per-view styles
    Tests/Unit/                             TSconfig, labels, templates, CSS
    Tests/Functional/                       Rendering of all six views

.. _developer-templates:

Template architecture
=====================

Each custom view is one template plus one card partial:

*   :file:`TimelineView.html` renders :file:`TimelineItem.html` per record
*   :file:`CatalogView.html` renders :file:`CatalogCard.html` per record

Everything a view template contains besides its record loop is the Records
module shell that EXT:records_list_types expects: the ``RecordFilters``
partial, the bulk-action form, :file:`TableHeadingBlock.html`, ``Pagination``
above and below the records, ``EmptyRecordsNotice`` and the *Expand table*
link in multi-table mode. The two templates keep that shell verbatim on
purpose, so each one reads as a complete, copyable example.

Both card partials share :file:`RecordCardActions.html` (edit, hide/unhide,
delete plus the parent ``RecordActionDropdown``) and
:file:`RecordCardTranslations.html` (one chip per site language). Their markup
uses the ``rle-record-card`` BEM block, styled in
:file:`record-card-shared.css`, which both view stylesheets import.

.. _developer-partial-names:

Partial names
=============

EXT:records_list_types appends ``templateRootPath`` and ``partialRootPath``
*after* its own paths, and Fluid resolves paths in reverse order. An own
partial named like a parent partial therefore replaces it everywhere. That is
why the partials here are called ``RecordCardActions`` and
``RecordCardTranslations`` rather than ``RecordActions`` and
``TranslationStrip``: the parent versions stay available and the built-in
views keep working. A unit test fails if a name collides.

.. _developer-contract:

Template contract
=================

Custom templates keep working across EXT:records_list_types releases as long as
they follow its systematic. The unit suite enforces these rules:

*   the markup is wrapped in ``<records-list-types-actions>`` so the shared
    JavaScript initializes
*   TYPO3-generated fragments (action buttons, multi-record-selection actions)
    are passed through
    ``f:sanitize.html(build: 'records-list-types-backend-fragments')`` and
    never printed unescaped
*   record editing uses ``typo3-backend-contextual-record-edit-trigger``
*   labels use translation domains, never ``LLL:`` paths or ``default``
    attributes that duplicate the text
*   visibility toggles carry state-aware accessible names
    (``action.hide`` / ``action.unhide``), and icon-only actions have an
    ``aria-label``
*   stylesheets use the ``--rle-*`` tokens defined on ``.rle-view`` and
    ``light-dark()``; no literal colours, no ``prefers-color-scheme`` blocks

.. _developer-add-view:

Adding another view
===================

#.  Register the view type in :file:`Configuration/page.tsconfig` with
    ``template``, ``templateRootPath``, ``partialRootPath``, ``css`` and a
    column configuration, and append its id with
    ``mod.web_list.viewMode.allowed := addToList(...)``.
#.  Copy :file:`CatalogView.html` and replace the grid container and the card
    partial; keep the surrounding shell as it is.
#.  Add your card partial and render :file:`RecordCardActions.html` and
    :file:`RecordCardTranslations.html` inside it.
#.  Add view-specific CSS that imports :file:`record-card-shared.css` with the
    release version as cache-buster query.
#.  Add the view to the data providers in
    :file:`Tests/Unit/Configuration/PageTsconfigTest.php` and
    :file:`Tests/Functional/View/ExampleViewRenderingTest.php`.

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

PHPStan runs at level 8 with strict rules and no baseline, and PHP-CS-Fixer
uses the TYPO3 coding standards.
