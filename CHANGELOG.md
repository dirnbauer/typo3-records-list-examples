# Changelog

All notable changes to `webconsulting/records-list-examples` are documented in
this file.

## 1.1.0 - 2026-06-02

### Changed

- Decompose custom view templates into layout-only entry points plus shared
  `RecordListTables`, record-list, record-card, title-row, and teaser partials.
- Render download buttons whenever the backend provides them; remove the
  catalog-only `showDownloadButton` flag from `TableHeadingBlock`.
- Align catalog hidden-badge text color with shared warning tokens.

## 1.0.2 - 2026-06-02

### Changed

- Extract shared Timeline and Catalog Fluid markup into
  `Resources/Private/Backend/Partials/` (`TableHeadingBlock`, `RecordActions`,
  `TranslationStrip`, and smaller UI fragments).
- Introduce `rle-record-card` BEM classes and `record-card-shared.css` for
  shared actions, checkboxes, and translation strips; view CSS files import it.
- Scope `.gitignore` `public/` to the TYPO3 web root so `Resources/Public/`
  extension assets remain tracked.
- Update README and TYPO3 documentation for the partial-based template
  architecture and custom-view extension workflow.

## 1.0.0 - 2026-05-24

### Added

- Initial stable release for TYPO3 14.3+.
- Six backend Records module view type examples: Timeline, Catalog, Address
  Book, Event List, Gallery, and Dashboard.
- Custom Timeline and Catalog Fluid templates with TYPO3 backend action
  patterns, translated labels, pagination support, and multi-record-selection
  support.
- View-specific CSS for Timeline and Catalog using TYPO3 backend CSS variables.
- English and German XLIFF 2.0 labels.
- TYPO3 documentation and local validation scripts.

### Changed

- Release metadata now lives in `composer.json`.
- The package requires PHP 8.3+ and `webconsulting/records-list-types` 1.0+.

### Removed

- Legacy `ext_emconf.php` metadata.

### Security

- No known security issues in this release.
