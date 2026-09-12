.. _example-views:

=============
Example views
=============

The extension registers six view types for the backend Records module. Two ship
their own Fluid templates and CSS, four only configure a template that
EXT:records_list_types already provides.

.. _example-views-overview:

Overview
========

..  list-table:: The six example views
    :header-rows: 1

    *   - View
        - Template
        - Records per page
        - Best for
    *   - Timeline
        - Own :file:`TimelineView`
        - 50
        - Events, changelog, history, milestones
    *   - Catalog
        - Own :file:`CatalogView`
        - 24
        - Products, portfolio, team members, media assets
    *   - Address book
        - Built-in :file:`CompactView`
        - 500
        - Contacts, members, employees, organizations
    *   - Event list
        - Built-in :file:`TeaserView`
        - 30
        - Events, conferences, meetups, deadlines
    *   - Gallery
        - Built-in :file:`GridView`
        - 48
        - Images, media files, artwork
    *   - Dashboard
        - Built-in :file:`GridView`
        - 20
        - Editorial overview, record management

.. _example-views-custom:

Views with their own template
=============================

Timeline
--------

A vertical timeline: a date column with date circles and a connecting line,
next to a content card per record. The date circle shows the first non-empty
``datetime`` display value and falls back to the record identifier.

Configured with ``displayColumns = label,datetime,teaser`` and
``columnsFromTCA = 0``, so the view is independent of the editor's
"Show columns" selection.

Catalog
-------

Large image cards with the title below the image. Records without a thumbnail
show a muted placeholder with the label *No image available*; records with a
thumbnail show the ``image.previewOnly`` hint from EXT:records_list_types,
because a backend thumbnail does not promise frontend output.

Hidden records keep the amber tint and warning bar that the built-in views use,
plus a *Hidden* badge on the image.

.. _example-views-builtin:

Views that reuse a built-in template
====================================

These four need no template of their own. They demonstrate that a view type is
often only a TSconfig block with a label, an icon, a template name, a
stylesheet and a column configuration:

*   **Address book** reuses :file:`CompactView` with a high
    ``itemsPerPage`` for dense contact lists.
*   **Event list** reuses :file:`TeaserView` with
    ``displayColumns = label,datetime,teaser``.
*   **Gallery** reuses :file:`GridView` with ``displayColumns = label`` so the
    thumbnails carry the view.
*   **Dashboard** reuses :file:`GridView` with ``columnsFromTCA = 1``, which
    hands column control to the editor's "Show columns" selection.

.. _example-views-behavior:

Shared backend behavior
=======================

All six views keep the Records module behavior editors expect, because they
render through EXT:records_list_types:

*   multi-record selection checkboxes and the bulk action bar
*   permission-aware actions: edit, hide and unhide, delete, info, history,
    copy and cut are rendered only where the backend user is allowed to use
    them
*   the TYPO3 contextual edit trigger
    (``typo3-backend-contextual-record-edit-trigger``) instead of legacy edit
    links
*   translation slots per site language, including the Core localization wizard
    for missing translations
*   sorting, filters and pagination, plus the shared JavaScript from
    EXT:records_list_types
*   single-table mode paginates, multi-table mode shows a preview with an
    *Expand table* link

.. _example-views-localization:

Localization
============

View names and descriptions ship as XLIFF 2.0 in English
(:file:`Resources/Private/Language/locallang.xlf`) and German
(:file:`de.locallang.xlf`), addressed through the translation domain
``records_list_examples.messages``.

All other texts in the custom templates come from the catalogs of
EXT:records_list_types (``records_list_types.messages``) and TYPO3 Core
(``core.core``), so both extensions use one term per concept. The templates
carry no hard-coded English; a unit test enforces that.
