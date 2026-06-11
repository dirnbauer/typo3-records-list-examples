# Changelog

All notable changes to `webconsulting/records-list-examples` are documented in
this file.

## 1.2.1 - 2026-06-11

### Fixed

- Catalog thumbnails rendered with an empty `src`: the thumbnail Fluid section
  was called without arguments and therefore had no `record` in scope. Every
  card showed a broken image instead of its preview.
- The `record-card-shared.css` `@import` now carries a version query as cache
  buster; TYPO3 only busts the parent file URL, so browsers kept serving the
  stale shared stylesheet after updates.

### Changed

- The "no image" placeholder is a solid muted surface with a readable pill
  label ("No image available" / "Kein Bild verfügbar") instead of an
  opacity-faded ghost on the checkerboard.
- Hidden timeline and catalog cards use the amber tint plus 3px warning bar
  shared with the records_list_types built-in views instead of an opacity
  fade; warning text switches to amber-400 in dark mode for WCAG 2.2 AA.
- Translation chips drop dashed borders for solid hairlines; the "add
  translation" chip renders as a recessed muted slot.

> Requires records_list_types >= 1.0.4 for the translation strip to render
> with this extension's own partial (older versions resolve the built-in
> `TranslationStrip` partial instead).

## 1.2.0 - 2026-06-02

### Changed

- Render only the first matching `displayValues` entry in `RecordTeaser` and the
  new `FirstDisplayValue` partial (timeline date circles).
- Deduplicate catalog thumbnail markup with a Fluid section; move the preview
  hint into the thumbnail branch.
- Apply shared `--rle-*` dark-mode warning and danger backgrounds to catalog
  and timeline containers; align catalog hidden badges with shared tokens.

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
