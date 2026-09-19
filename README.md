# Records List Examples

[![CI](https://github.com/dirnbauer/typo3-records-list-examples/actions/workflows/ci.yml/badge.svg)](https://github.com/dirnbauer/typo3-records-list-examples/actions/workflows/ci.yml)
[![TYPO3 14](https://img.shields.io/badge/TYPO3-14.3-orange)](https://get.typo3.org/version/14)
[![PHP 8.4](https://img.shields.io/badge/PHP-8.4%2B-777bb4)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0--or--later-blue)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)

Six ready-to-use view types for the TYPO3 v14 backend **Records** module, and a
worked reference for writing your own on top of
[Records List Types](https://github.com/dirnbauer/typo3-records-list-types).

## What it is

| View | Template | Per page | Good for |
| --- | --- | --- | --- |
| Timeline | own `TimelineView` | 50 | Events, changelog, history |
| Catalog | own `CatalogView` | 24 | Products, portfolio, team, media |
| Address book | built-in `CompactView` | 500 | Contacts, members, organizations |
| Event list | built-in `TeaserView` | 30 | Conferences, meetups, deadlines |
| Gallery | built-in `GridView` | 48 | Images, media files, artwork |
| Dashboard | built-in `GridView` | 20 | Editorial overview, record management |

Timeline and Catalog ship their own Fluid template, partials and CSS. The other
four are a TSconfig block each — which is the point: most view types need no
template at all. All six keep the behaviour editors expect from the Records
module (multi-record selection, permission-aware actions, contextual editing,
translations, filters, sorting, pagination), because they render through
Records List Types. The package contributes no PHP runtime class.

## Requirements

| | |
| --- | --- |
| TYPO3 | 14.3 or later (v14 series) |
| PHP | 8.4 or 8.5 |
| Records List Types | 1.1 or later |
| Installation | Composer mode only |

## Install

Neither package is on Packagist, so add both VCS repositories first:

```bash
composer config repositories.records-list-types vcs https://github.com/dirnbauer/typo3-records-list-types.git
composer config repositories.records-list-examples vcs https://github.com/dirnbauer/typo3-records-list-examples.git
composer require webconsulting/records-list-examples:^1.4
vendor/bin/typo3 extension:setup -e records_list_examples
vendor/bin/typo3 cache:flush
```

## Configure

`Configuration/page.tsconfig` is loaded automatically and appends the six views
to `mod.web_list.viewMode.allowed`. Override anything in your site's Page
TSconfig:

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

Full option reference: [configuration](Documentation/Configuration/Index.rst).

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

PHPStan runs at level 8 with strict rules and no baseline, PHP-CS-Fixer uses
the TYPO3 coding standards. The functional suite boots Records List Types
together with this extension and renders all six views through the Records
module controller; the unit suite guards the TSconfig, the label catalog, the
template contract, the stylesheets and the manual.

Labels live in `Resources/Private/Language/locallang.xlf` and are referenced as
`records_list_examples.messages:key`; shared texts come from
`records_list_types.messages` and `core.core`, so both extensions use one term
per concept. Adding a view of your own is described in
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
