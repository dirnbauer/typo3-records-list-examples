.. _configuration:

=============
Configuration
=============

TYPO3 loads :file:`Configuration/page.tsconfig` of this extension on every
page. That single file registers the six view types and adds them to the
allowed views. No import, no :php:`ExtensionManagementUtility` call.

.. _configuration-view-types:

Allowed views
=============

The example views are *appended* to whatever EXT:records_list_types already
allows, instead of replacing the list:

.. code-block:: typoscript
    :caption: Configuration/page.tsconfig

    mod.web_list.viewMode.allowed := addToList(timeline,catalog,addressbook,eventlist,gallery,dashboard)

Assigning the list would work as well, but it would silently drop a built-in
view as soon as EXT:records_list_types adds one. The result on a default
installation is:

.. code-block:: text

    list, grid, compact, teaser, timeline, catalog, addressbook, eventlist, gallery, dashboard

.. note::

    ``mod.web_list.viewMode.allowed`` replaced ``mod.web_list.allowedViews`` in
    EXT:records_list_types 1.1.0. The old key still works but logs a
    deprecation. It remains a compatibility alias in version 2.0; use
    ``viewMode.allowed`` in new configuration.

Restrict the available views per page with TYPO3 Page TSconfig conditions:

.. code-block:: typoscript
    :caption: Restrict a page to the timeline view

    [page["uid"] == 42]
        mod.web_list.viewMode.allowed = list,timeline
        mod.web_list.viewMode.default = timeline
    [end]

.. _configuration-options:

View type options
=================

Every view is registered under ``mod.web_list.viewMode.types.{id}``. The
Timeline registration shows all options this extension uses:

.. code-block:: typoscript
    :caption: The timeline view type

    mod.web_list.viewMode.types.timeline {
        label = records_list_examples.messages:viewMode.timeline
        description = records_list_examples.messages:viewMode.timeline.description
        icon = content-timeline
        template = TimelineView
        templateRootPath = EXT:records_list_examples/Resources/Private/Backend/Templates/
        partialRootPath = EXT:records_list_examples/Resources/Private/Backend/Partials/
        css = EXT:records_list_examples/Resources/Public/Css/timeline.css
        columnsFromTCA = 0
        displayColumns = label,datetime,teaser
        itemsPerPage = 50
    }

``label`` and ``description`` use TYPO3 14 translation domains. The four views
that reuse a built-in template set neither ``templateRootPath`` nor
``partialRootPath``; they point ``template`` at ``CompactView``, ``TeaserView``
or ``GridView`` and ``css`` at the matching stylesheet of
EXT:records_list_types, because a new type id does not inherit the stylesheet
of the template it reuses.

Override single options in your site's Page TSconfig, for example the page
size:

.. code-block:: typoscript
    :caption: Change records per page

    mod.web_list.viewMode.types.timeline.itemsPerPage = 100
    mod.web_list.viewMode.types.catalog.itemsPerPage = 12
    mod.web_list.viewMode.types.gallery.itemsPerPage = 96

.. _configuration-images:

Image fields
============

Catalog and Gallery show thumbnails from FAL fields. Configure the field
mapping per table, as documented by EXT:records_list_types:

.. code-block:: typoscript
    :caption: Configure product thumbnails

    mod.web_list.gridView.table.tx_myshop_domain_model_product {
        titleField = name
        descriptionField = short_description
        imageField = images
        preview = 1
    }

.. _configuration-templates:

Custom template paths
=====================

EXT:records_list_types appends ``templateRootPath`` and ``partialRootPath``
after its own paths, and Fluid resolves paths in reverse order. Parent
partials such as ``TableHeading``, ``RecordFilters`` and ``Pagination`` stay
available, while an own partial with a parent name would replace it for every
view. See :ref:`developer-partial-names`.

When adding another view of your own, see :ref:`developer-add-view`.
