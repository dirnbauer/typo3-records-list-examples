# Records List Examples for TYPO3 v14

Example view types for the [Records List Types](https://github.com/dirnbauer/typo3-records-list-types) extension. Install this extension to get 6 additional custom view types in the TYPO3 backend Records module -- ready to use, no extra PHP needed.

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

The two custom templates follow the current `records_list_types` template systematic, so they keep working as the main extension evolves:

- shared `TableHeading` partial for the table heading (single-table mode + multi-record-selection panel)
- TYPO3 core `f:sanitize.html(build: 'records-list-types-backend-fragments')` for TYPO3-generated backend fragments (action buttons, multi-record-selection actions)
- TYPO3 14 native `<typo3-backend-contextual-record-edit-trigger>` for record edit links
- permission-aware action rendering (`record.permissions.canEdit`, `canToggleVisibility`, `canDelete`, `canShowInfo`, `canHistory`, `canCopy`)
- "More actions" popover that reuses the same pattern as the records_list_types built-in templates
- shared `Pagination` partial for single-table pagination
- `<records-list-types-actions>` Lit host so the shared action JavaScript initializes custom templates

That means the custom templates keep TYPO3 backend behavior that editors already expect:

- Multi Record Selection checkboxes and action bar
- visibility / delete / info / history / copy / cut actions only when the current backend user is allowed to use them
- TYPO3's native contextual edit sheet instead of legacy edit links
- shared sorting, pagination input handling, scroll-shadow checks, and action binding from `GridViewActions.js`
- translated labels for view types and template UI via XLIFF (`en` + `de`)

All views follow TYPO3 Core pagination behavior: multi-table mode shows a preview with "Expand table" button, single-table mode shows full pagination.

## Requirements

- TYPO3 v14.0+
- PHP 8.3+
- [webconsulting/records-list-types](https://github.com/dirnbauer/typo3-records-list-types) (installed automatically as dependency)

## Installation

```bash
composer require webconsulting/records-list-examples
```

This automatically installs `webconsulting/records-list-types` if not already present.

Activate the extension:

```bash
./vendor/bin/typo3 extension:activate records_list_examples
```

After activation, the 6 new custom view types appear in the view switcher in **Content > Records**.

## Localization

All view-type labels (Timeline, Catalog, Address Book, Event List, Gallery, Dashboard) and descriptions are translatable via XLIFF and shipped in:

- `Resources/Private/Language/locallang.xlf` (English, default)
- `Resources/Private/Language/de.locallang.xlf` (German)

The custom Timeline and Catalog templates also use translated labels for action buttons and template strings (`No image`, `Hidden`, `Edit`, `Show`, `Hide`, `Delete`, `More actions`, `Info`, `History`, `Copy`, `Cut`). The image preview hint reuses the existing `image.previewOnly` translation from the main `records_list_types` extension.

To override or extend the translations, drop your own `locallang.xlf` overrides into your sitepackage and TYPO3 will pick them up via the standard XLIFF override mechanism.

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
├── Resources/
│   ├── Private/
│   │   ├── Backend/
│   │   │   └── Templates/
│   │   │       ├── TimelineView.html       # Timeline: vertical timeline layout
│   │   │       └── CatalogView.html        # Catalog: large image card grid
│   │   └── Language/
│   │       ├── locallang.xlf               # English labels (view types + template strings)
│   │       └── de.locallang.xlf            # German translations
│   └── Public/Css/
│       ├── timeline.css                    # Timeline styles (dark mode support)
│       └── catalog.css                     # Catalog styles (dark mode support)
├── composer.json
├── ext_emconf.php
└── README.md
```

## How It Works

This extension contains **zero PHP classes**. It registers custom view types purely through TSconfig and Fluid templates:

- **TSconfig** (`setup.tsconfig`) -- registers 6 view types with translated labels (`LLL:` references), icons, templates, CSS, and column configuration
- **Templates** (`TimelineView.html`, `CatalogView.html`) -- custom Fluid templates for Timeline and Catalog using the current `records_list_types` heading/sanitizer/permissions/popover systematic
- **CSS** (`timeline.css`, `catalog.css`) -- view-specific styles using TYPO3 CSS variables for dark mode
- **XLIFF** (`locallang.xlf`, `de.locallang.xlf`) -- translatable labels for view types, descriptions, action buttons, and template strings

The other 4 views (Address Book, Event List, Gallery, Dashboard) reuse the built-in templates (`CompactView`, `TeaserView`, `GridView`) from `records_list_types` -- they only need TSconfig configuration.

This is the pattern for creating your own custom view types: TSconfig + optional template + optional CSS. The two custom templates in this repo demonstrate the current `records_list_types` systematic for:

- shared `TableHeading` partial rendering
- TYPO3 core `f:sanitize.html(build: 'records-list-types-backend-fragments')` for TYPO3/core-generated backend fragments
- TYPO3 14 native contextual record edit trigger (`<typo3-backend-contextual-record-edit-trigger>`)
- permission-aware actions (`record.permissions.*`)
- "More actions" popover button (`popovertarget` + `popover`) that mirrors the built-in Card / TeaserCard / CompactRow partials
- compatibility with the built-in `Pagination` partial and Multi Record Selection handling
- wrapping custom view markup in `<records-list-types-actions>` for the Lit-based shared action module

See the [Custom View Types documentation](https://github.com/dirnbauer/typo3-records-list-types/blob/main/Documentation/CustomViewTypes.md) for full details.

## Validation

For lightweight checks during development, these commands are enough:

```bash
composer validate --no-check-publish
php -r 'var_dump(simplexml_load_file("Resources/Private/Language/locallang.xlf") !== false);'
php -r 'var_dump(simplexml_load_file("Resources/Private/Language/de.locallang.xlf") !== false);'
```

This validates the Composer metadata and confirms that both XLIFF files are well-formed XML.

## License

GPL-2.0-or-later

## Author

**Webconsulting** -- office@webconsulting.at
