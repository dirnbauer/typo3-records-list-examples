# Records List Examples for TYPO3

Example view types for the [Records List Types](https://github.com/dirnbauer/typo3-records-list-types) extension. Install this extension to get 6 additional custom view types in the TYPO3 backend Records module -- ready to use, no extra PHP needed.

This package is **TYPO3 v14 only**. Version 1.1.0 targets TYPO3 14.3 LTS and later, uses Records List Types 1.0 or later, and intentionally drops TYPO3 v13 support.

## View Types

| View | Template | Description |
|------|----------|-------------|
| **Timeline** | Custom | Classic vertical timeline with date circles, connecting lines, and content cards with chevron connectors |
| **Catalog** | Custom | Large 4:3 image cards with hover zoom, "No image" placeholder, and preview hint for editors |
| **Address Book** | CompactView | Dense contact list with fixed columns, 500 records per page |
| **Event List** | TeaserView | Date-focused event cards with calendar icon, 30 per page |
| **Gallery** | GridView | Photo gallery with large thumbnails, minimal text, 48 per page |
| **Dashboard** | GridView | Editor-controlled columns via TYPO3's "Show columns" selector |

**Timeline** and **Catalog** have custom templates and CSS with full dark mode support. The other 4 reuse built-in templates with different configurations -- demonstrating that many custom view types need only TSconfig and optional assets.

**Timeline** and **Catalog** share backend chrome through Fluid partials in
`Resources/Private/Backend/Partials/` and a common `rle-record-card` BEM block
for actions, checkboxes, and translation strips. View-specific templates only
contain layout that genuinely differs (image cards vs timeline date column).

The custom templates follow the current `records_list_types` template systematic, so they keep working as the main extension evolves:

- extension-local partials (`TableHeadingBlock`, `RecordActions`, `TranslationStrip`, …) plus parent partials from `records_list_types` (`TableHeading`, `RecordFilters`, `Pagination`)
- shared `TableHeading` partial for the table heading (single-table mode + multi-record-selection panel)
- TYPO3 core `f:sanitize.html(build: 'records-list-types-backend-fragments')` for TYPO3-generated backend fragments (action buttons, multi-record-selection actions)
- TYPO3 14 native `<typo3-backend-contextual-record-edit-trigger>` for record edit links
- permission-aware action rendering (`record.permissions.canEdit`, `canToggleVisibility`, `canDelete`, `canShowInfo`, `canHistory`, `canCopy`)
- "More actions" popover that reuses the same pattern as the records_list_types built-in templates
- shared `Pagination` partial for single-table pagination
- shared JavaScript hooks from the main `records_list_types` extension for backend record actions

That means the custom templates keep TYPO3 backend behavior that editors already expect:

- Multi Record Selection checkboxes and action bar
- visibility / delete / info / history / copy / cut actions only when the current backend user is allowed to use them
- TYPO3's native contextual edit sheet instead of legacy edit links
- shared sorting, pagination input handling, scroll-shadow checks, and action binding from `GridViewActions.js`
- translated labels for view types and template UI via XLIFF 2.0 (`en` + `de`)

All views follow TYPO3 Core pagination behavior: multi-table mode shows a preview with "Expand table" button, single-table mode shows full pagination.

## Requirements

- TYPO3 v14.3+
- PHP 8.3 through 8.5
- [webconsulting/records-list-types](https://github.com/dirnbauer/typo3-records-list-types) 1.0+

The extension depends explicitly on TYPO3 Core, Backend, Record List, and Fluid packages because its templates use TYPO3 backend components, the Records module, and Fluid ViewHelpers directly.

## Installation

The examples package is installed from GitHub when it is not available via
Packagist. Add both VCS repositories in your TYPO3 project's root
`composer.json`; Composer does not inherit repository definitions from
dependencies. For GitHub VCS installations, Composer must be able to see stable
release tags for both this package and `webconsulting/records-list-types`.

```bash
composer config repositories.records-list-types vcs https://github.com/dirnbauer/typo3-records-list-types.git
composer config repositories.records-list-examples vcs https://github.com/dirnbauer/typo3-records-list-examples.git
composer require webconsulting/records-list-examples:^1.0
```

This automatically installs `webconsulting/records-list-types` if not already present.

Set up the extension and clear TYPO3 caches:

```bash
./vendor/bin/typo3 extension:setup -e records_list_examples
./vendor/bin/typo3 cache:flush
```

After setup, the 6 new custom view types appear in the view switcher in **Content > Records**.

## Localization

All view-type labels (Timeline, Catalog, Address Book, Event List, Gallery, Dashboard) and descriptions are translatable via XLIFF 2.0 and shipped in:

- `Resources/Private/Language/locallang.xlf` (English, default)
- `Resources/Private/Language/de.locallang.xlf` (German)

The custom Timeline and Catalog templates also use translated labels for action buttons and template strings (`No image`, `Hidden`, `Edit`, `Show`, `Hide`, `Delete`, `More actions`, `Info`, `History`, `Copy`, `Cut`). The image preview hint reuses the existing `image.previewOnly` translation from the main `records_list_types` extension.

To override or extend the translations, drop your own `locallang.xlf` overrides into your sitepackage and TYPO3 will pick them up via the standard XLIFF override mechanism.

## JavaScript

This examples extension does not ship custom JavaScript and does not use Lit directly. Timeline and Catalog are intentionally implemented with Fluid templates and CSS only.

Interactive behavior such as record actions, visibility toggles, pagination input handling, multi-record selection, and TYPO3 backend web components is provided by `records_list_types` and TYPO3 core. Custom templates should keep using the documented `data-gridview-action` attributes and TYPO3 backend elements so they continue to work with the shared JavaScript from the main extension.

If a future custom view needs genuinely stateful client-side behavior, add a dedicated ES module via the view type `js` option and load it only for that view. Lit should stay out of this examples package unless such a view has a concrete need for a web component.

## Customization

### Restrict views to specific pages

By default all 6 views are available everywhere. To limit which views appear on which pages, override the `allowed` setting in your Page TSconfig:

```tsconfig
# Only timeline on the events page
[page["uid"] == 42]
    mod.web_list.viewMode.allowed = list,timeline
    mod.web_list.viewMode.default = timeline
[end]

# Only catalog on the shop page tree
[page["uid"] == 100 || page["pid"] == 100]
    mod.web_list.viewMode.allowed = list,catalog
    mod.web_list.viewMode.default = catalog
[end]
```

### Configure per-table image fields

The Catalog and Gallery views show thumbnails from FAL image fields. Configure which field to use per table:

```tsconfig
mod.web_list.gridView.table.tx_myshop_domain_model_product {
    titleField = name
    descriptionField = short_description
    imageField = images
    preview = 1
}
```

### Modify items per page

Each view type has its own default. Override via TSconfig:

```tsconfig
mod.web_list.viewMode.types.timeline.itemsPerPage = 100
mod.web_list.viewMode.types.catalog.itemsPerPage = 12
mod.web_list.viewMode.types.gallery.itemsPerPage = 96
```

## File Structure

```
records_list_examples/
├── Configuration/
│   ├── page.tsconfig                       # Loads the setup.tsconfig
│   └── TsConfig/Page/
│       └── setup.tsconfig                  # All 6 view type registrations
├── Build/
│   └── Scripts/
│       ├── runTests.sh                     # Local suite runner
│       └── validate-xlf.php                # XLIFF validation
├── Documentation/                          # TYPO3 documentation
├── Resources/
│   ├── Private/
│   │   ├── Backend/
│   │   │   ├── Partials/                   # Shared Fluid partials (both custom views)
│   │   │   │   ├── RecordListTables.html   # Table shell: filters, form, pagination
│   │   │   │   ├── CatalogRecordList.html  # Catalog grid wrapper
│   │   │   │   ├── CatalogRecordCard.html  # Single catalog card
│   │   │   │   ├── TimelineRecordList.html # Timeline list wrapper
│   │   │   │   ├── TimelineRecordItem.html # Single timeline item
│   │   │   │   ├── TableHeadingBlock.html  # Heading + multi-record-selection bar
│   │   │   │   ├── RecordTitleRow.html     # Title, UID, optional icon
│   │   │   │   ├── RecordTeaser.html       # Cropped text teaser
│   │   │   │   ├── RecordActions.html      # Edit / visibility / delete / more-actions
│   │   │   │   ├── TranslationStrip.html   # Per-language translation slots
│   │   │   │   ├── MultiRecordCheckbox.html
│   │   │   │   ├── ExpandTableLink.html
│   │   │   │   └── NoRecordsCallout.html
│   │   │   └── Templates/
│   │   │       ├── TimelineView.html       # Layout-only entry (renders RecordListTables)
│   │   │       └── CatalogView.html        # Layout-only entry (renders RecordListTables)
│   │   └── Language/
│   │       ├── locallang.xlf               # English labels (view types + template strings)
│   │       └── de.locallang.xlf            # German translations
│   └── Public/Css/
│       ├── record-card-shared.css          # Shared actions, checkbox, translations
│       ├── timeline.css                    # Timeline layout (imports shared CSS)
│       └── catalog.css                     # Catalog layout (imports shared CSS)
├── composer.json
├── phpstan.neon
└── README.md
```

Custom view types set `partialRootPath` to the extension `Partials/` folder.
`records_list_types` prepends that path and still resolves its own partials
(`TableHeading`, `RecordFilters`, `Pagination`) from the parent extension.

## How It Works

This extension contains **zero PHP classes**. It registers custom view types purely through TSconfig and Fluid templates:

- **TSconfig** (`setup.tsconfig`) -- registers 6 view types with translated labels (`LLL:` references), icons, templates, CSS, and column configuration
- **Templates** (`TimelineView.html`, `CatalogView.html`) -- view-specific layout only; shared chrome lives in `Partials/`
- **Partials** -- reusable record actions, translation strips, table heading block, and empty-state UI
- **CSS** (`record-card-shared.css`, `timeline.css`, `catalog.css`) -- shared record-card chrome plus view-specific layout, using TYPO3 CSS variables for dark mode
- **XLIFF 2.0** (`locallang.xlf`, `de.locallang.xlf`) -- translatable labels for view types, descriptions, action buttons, and template strings

The other 4 views (Address Book, Event List, Gallery, Dashboard) reuse the built-in templates (`CompactView`, `TeaserView`, `GridView`) from `records_list_types` -- they only need TSconfig configuration.

### Add a third custom view

1. Register the view type in `Configuration/TsConfig/Page/setup.tsconfig` with `template`, `templateRootPath`, `partialRootPath`, and `css`.
2. Add a record-list partial (for example `KanbanRecordList.html`) with the view-specific record layout.
3. Add a layout-only template that renders `RecordListTables` with your `recordListPartial` name.
4. Reuse `RecordTitleRow`, `RecordTeaser`, `RecordActions`, `TranslationStrip`, and the other shared partials where applicable.
5. Add view-specific CSS; `@import` `record-card-shared.css` for shared chrome.

This is the pattern for creating your own custom view types: TSconfig + optional template + optional partials + optional CSS. The two custom templates in this repo demonstrate the current `records_list_types` systematic for:

- shared `TableHeading` partial rendering
- TYPO3 core `f:sanitize.html(build: 'records-list-types-backend-fragments')` for TYPO3/core-generated backend fragments
- TYPO3 14 native contextual record edit trigger (`<typo3-backend-contextual-record-edit-trigger>`)
- permission-aware actions (`record.permissions.*`)
- "More actions" popover button (`popovertarget` + `popover`) that mirrors the built-in Card / TeaserCard / CompactRow partials
- compatibility with the built-in `Pagination` partial and Multi Record Selection handling

These templates are backend Records module examples. They intentionally do not render frontend page content areas and should not use Visual Editor page ViewHelpers such as `f:render.contentArea` or `f:mark.contentArea`. Bootstrap, shadcn/ui, and other frontend theme presets belong in a sitepackage such as Desiderio, not in this backend examples package.

See the [Custom View Types documentation](https://github.com/dirnbauer/typo3-records-list-types/blob/main/Documentation/CustomViewTypes.md) for full details.

## Validation

Run the local CI checks during development:

```bash
composer install
composer ci
```

This validates Composer metadata, validates both XLIFF files, runs Composer
audit, and runs PHPStan at level `max`.

The same checks run in GitHub Actions:

- Composer validation and `composer audit`
- XLIFF validation
- PHPStan level `max`
- PHP 8.3, 8.4, and 8.5 matrix coverage

See [CHANGELOG.md](CHANGELOG.md) for release notes and [Documentation/](Documentation/) for the TYPO3 manual.

## License

GPL-2.0-or-later

## Author

**Webconsulting** -- office@webconsulting.at
