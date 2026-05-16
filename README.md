# Records List Examples for TYPO3 v14

Example view types for the [Records List Types](https://github.com/dirnbauer/typo3-records-list-types) extension. Install this extension to get 6 additional view modes in the TYPO3 backend Records module -- ready to use, no configuration needed.

## View Types

| View | Template | Description |
|------|----------|-------------|
| **Timeline** | Custom | Classic vertical timeline with date circles, connecting lines, and content cards with chevron connectors |
| **Catalog** | Custom | Large 4:3 image cards with hover zoom, "No image" placeholder, and preview hint for editors |
| **Address Book** | CompactView | Dense contact list with fixed columns, 500 records per page |
| **Event List** | TeaserView | Date-focused event cards with calendar icon, 30 per page |
| **Gallery** | GridView | Photo gallery with large thumbnails, minimal text, 48 per page |
| **Dashboard** | GridView | Editor-controlled columns via TYPO3's "Show columns" selector |

**Timeline** and **Catalog** have custom templates and CSS with full dark mode support. The other 4 reuse built-in templates with different configurations -- demonstrating that new view types often need zero custom files.

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

After activation, the 6 new view types appear in the view mode switcher in **Content > Records**.

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
│   └── TsConfig/Page/
│       └── setup.tsconfig              # All 6 view type registrations
├── Resources/
│   ├── Private/Backend/
│   │   └── Templates/
│   │       ├── TimelineView.html       # Timeline: vertical timeline layout
│   │       └── CatalogView.html        # Catalog: large image card grid
│   └── Public/Css/
│       ├── timeline.css                # Timeline styles (dark mode support)
│       └── catalog.css                 # Catalog styles (dark mode support)
├── composer.json
├── ext_emconf.php
├── ext_localconf.php
└── README.md
```

## How It Works

This extension contains **zero PHP classes**. It registers view types purely through TSconfig and Fluid templates:

- **TSconfig** (`setup.tsconfig`) -- registers 6 view types with labels, icons, templates, CSS, and column configuration
- **Templates** (`TimelineView.html`, `CatalogView.html`) -- custom Fluid templates for Timeline and Catalog
- **CSS** (`timeline.css`, `catalog.css`) -- view-specific styles using TYPO3 CSS variables for dark mode

The other 4 views (Address Book, Event List, Gallery, Dashboard) reuse the built-in templates (`CompactView`, `TeaserView`, `GridView`) from `records_list_types` -- they only need TSconfig configuration.

This is the pattern for creating your own view types: TSconfig + optional template + optional CSS. See the [Custom View Types documentation](https://github.com/dirnbauer/typo3-records-list-types/blob/main/Documentation/CustomViewTypes.md) for full details.

## License

GPL-2.0-or-later

## Author

**Webconsulting** -- office@webconsulting.at
