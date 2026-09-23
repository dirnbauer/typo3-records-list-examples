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
    Resources/Private/Backend/Partials/     TimelineItem, CatalogCard
    Resources/Private/Language/             XLIFF 2.0 labels (en, de)
    Resources/Public/Css/                   timeline.css, catalog.css
    Tests/Unit/                             TSconfig, labels, templates, CSS
    Tests/Functional/                       Rendering of all six views

.. _developer-templates:

Template architecture
=====================

Each custom view is one template plus one card partial:

*   :file:`TimelineView.html` renders :file:`TimelineItem.html` per record
*   :file:`CatalogView.html` renders :file:`CatalogCard.html` per record

Each table is framed by the ``Table/Section`` partial of
EXT:records_list_types, which renders it like a table of the List View:
filters, heading with the table actions and the sorting mode, the selection
bar, workspace notices, pagination above and below the records, the empty
state and the *Expand table* link. A view template only renders its records,
as the child content (``contentAs="body"``), plus Core's selection menu
(``Table/SelectionToggle``).

The card partials build a record from the parent's ``Record/*`` partials, so
they show what the List View shows: the selection checkbox, the record icon
with its context menu, the title (contextual edit), the state badges, Core's
control panel and, through ``TranslationStrip``, the translations. Only the
arrangement and the ``rle-*`` layout classes are this extension's.

.. _developer-partial-names:

Partial names
=============

EXT:records_list_types appends ``templateRootPath`` and ``partialRootPath``
*after* its own paths, and Fluid resolves paths in reverse order. An own
partial named like a parent partial therefore replaces it everywhere, the
built-in views included. The partials here carry names of their own
(``TimelineItem``, ``CatalogCard``); a unit test fails if a name collides.

.. _developer-contract:

Template contract
=================

Custom templates keep working across EXT:records_list_types releases as long as
they follow its systematic. The unit suite enforces these rules:

*   the markup is wrapped in ``<records-list-types-actions>`` so the shared
    JavaScript initializes
*   every table is rendered through ``Table/Section`` and every card through
    the ``Record/*`` partials; templates print nothing unescaped themselves
*   cards are named by their title (``aria-labelledby``) and titles use
    heading elements
*   labels use translation domains, never ``LLL:`` paths or ``default``
    attributes that duplicate the text
*   stylesheets use TYPO3's design tokens (``--typo3-*``) only: no literal
    colours, no ``light-dark()`` of their own, no ``prefers-color-scheme``
    blocks, no ``@import``; every styled class is rendered and vice versa

.. _developer-add-view:

Adding another view
===================

#.  Register the view type in :file:`Configuration/page.tsconfig` with
    ``template``, ``templateRootPath``, ``partialRootPath``, ``css`` and a
    column configuration, and append its id with
    ``mod.web_list.viewMode.allowed := addToList(...)``.
#.  Copy :file:`CatalogView.html` and replace the grid container and the card
    partial; keep the ``Table/Section`` call as it is.
#.  Add your card partial and build it from the parent's ``Record/*``
    partials and ``TranslationStrip``.
#.  Add view-specific CSS with TYPO3's design tokens.
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

The extension ships no JavaScript. Record actions, the context menu,
visibility changes, pagination and multi-record selection come from TYPO3
Core and EXT:records_list_types.

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
