.. _configuration:

=============
Configuration
=============

The extension loads its default Page TSconfig from:

.. code-block:: text
    :caption: Default Page TSconfig entrypoint

    Configuration/page.tsconfig

That file imports:

.. code-block:: text
    :caption: View type registration

    Configuration/TsConfig/Page/setup.tsconfig

.. _configuration-view-types:

View types
==========

The default configuration enables all example view types alongside the built-in
list, grid, compact, and teaser views:

.. code-block:: typoscript
    :caption: Enabled backend Records module views

    mod.web_list.viewMode.allowed = list,grid,compact,teaser,timeline,catalog,addressbook,eventlist,gallery,dashboard

Restrict the available views per page with TYPO3 Page TSconfig conditions:

.. code-block:: typoscript
    :caption: Restrict a page to timeline view

    [page["uid"] == 42]
        mod.web_list.viewMode.allowed = list,timeline
        mod.web_list.viewMode.default = timeline
    [end]

.. _configuration-custom-views:

Custom view paths
=================

Timeline and Catalog register custom template and partial roots in
:file:`Configuration/TsConfig/Page/setup.tsconfig`:

.. code-block:: typoscript
    :caption: Custom view type paths

    types.timeline {
        template = TimelineView
        templateRootPath = EXT:records_list_examples/Resources/Private/Backend/Templates/
        partialRootPath = EXT:records_list_examples/Resources/Private/Backend/Partials/
        css = EXT:records_list_examples/Resources/Public/Css/timeline.css
    }

When adding another custom view, reuse :file:`RecordListTables.html` and the
existing record chrome partials. Add a new record-list partial for the view-specific
layout, a layout-only template that renders :file:`RecordListTables.html`, and
view-specific CSS. Import :file:`record-card-shared.css` from the new stylesheet
to inherit the shared record-card chrome.

.. _configuration-images:

Image fields
============

Catalog and Gallery can show thumbnails from FAL fields. Configure the field
mapping per table:

.. code-block:: typoscript
    :caption: Configure product thumbnails

    mod.web_list.gridView.table.tx_myshop_domain_model_product {
        titleField = name
        descriptionField = short_description
        imageField = images
        preview = 1
    }

.. _configuration-quality:

Quality checks
==============

Run the local checks with:

.. code-block:: bash
    :caption: Local CI

    composer ci

The CI command validates Composer metadata, validates XLIFF files, runs
Composer audit, and runs PHPStan at level ``max``.
