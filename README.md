# Records List Examples

Six ready-to-use view types for the TYPO3 v14 backend **Records** module, on
top of [Records List Types](https://github.com/dirnbauer/typo3-records-list-types):
Timeline, Catalog, Address book, Event list, Gallery and Dashboard.

## What it is

- **Timeline** and **Catalog** ship their own Fluid templates and CSS: a date
  column with content cards, and large image cards with placeholders and
  preview hints.
- **Address book**, **Event list**, **Gallery** and **Dashboard** only
  configure the built-in `CompactView`, `TeaserView` and `GridView` templates —
  a view type is often just a TSconfig block.
- All six keep the Records module behavior editors expect: multi-record
  selection, permission-aware actions, contextual editing, translations,
  filters, sorting and pagination.
- Zero PHP runtime classes. Page TSconfig, Fluid, CSS and XLIFF 2.0 labels
  (English and German) are all it contributes.

## Requirements

- TYPO3 14.3.6 or later (v14 series)
- PHP 8.4 or 8.5
- [webconsulting/records-list-types](https://github.com/dirnbauer/typo3-records-list-types) 1.1 or later
- Composer mode

## Install

Neither package is on Packagist, so add both VCS repositories first:

```bash
composer config repositories.records-list-types vcs https://github.com/dirnbauer/typo3-records-list-types.git
composer config repositories.records-list-examples vcs https://github.com/dirnbauer/typo3-records-list-examples.git
composer require webconsulting/records-list-examples:^1.3
vendor/bin/typo3 extension:setup -e records_list_examples
vendor/bin/typo3 cache:flush
```

## Configure

The extension ships `Configuration/page.tsconfig` and enables every example
view. Override it in your site's Page TSconfig:

```typoscript
# Only the timeline on the events page
[page["uid"] == 42]
    mod.web_list.viewMode.allowed = list,timeline
    mod.web_list.viewMode.default = timeline
[end]

mod.web_list.viewMode.types.catalog.itemsPerPage = 12

# Thumbnails for Catalog and Gallery
mod.web_list.gridView.table.tx_myshop_domain_model_product {
    titleField = name
    descriptionField = short_description
    imageField = images
    preview = 1
}
```

`mod.web_list.viewMode.allowed` replaced `mod.web_list.allowedViews` in
Records List Types 1.1.0. Full option reference:
[configuration](Documentation/Configuration/Index.rst).

## Use

Open **Content → Records** and pick a view from the **View** dropdown in the
module header. Single-table views paginate; multi-table views show a preview
with an *Expand table* link. What each view is good for is listed in the
[example views](Documentation/ExampleViews/Index.rst) manual.

## Develop

```bash
composer install
Build/Scripts/runTests.sh -s ci          # composer, lint, cgl, phpstan, unit
Build/Scripts/runTests.sh -s functional  # SQLite by default
```

PHPStan runs at level 8 with strict rules, PHP-CS-Fixer uses the TYPO3 coding
standards, and CI covers PHP 8.4 (8.5 as an allowed failure) with functional
tests on MariaDB 10.11. The functional suite boots Records List Types together
with this extension and renders all six views; the unit suite guards the
TSconfig presets, the label catalog and the template contract.

Labels live in `Resources/Private/Language/locallang.xlf` and are referenced as
`records_list_examples.messages:key`; shared texts come from
`records_list_types.messages` and `core.core`, so both extensions use one term
per concept. Adding your own view is described in
[developer](Documentation/Developer/Index.rst).

## Docs

The RST manual in [Documentation](Documentation/Index.rst) is the canonical
reference: [introduction](Documentation/Introduction/Index.rst),
[installation](Documentation/Installation/Index.rst),
[example views](Documentation/ExampleViews/Index.rst),
[configuration](Documentation/Configuration/Index.rst) and
[developer](Documentation/Developer/Index.rst). Changes are recorded in
[CHANGELOG.md](CHANGELOG.md).

## License

GPL-2.0-or-later · [Webconsulting](https://github.com/dirnbauer/typo3-records-list-examples)
