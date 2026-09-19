# Changelog

All notable changes to `webconsulting/records-list-examples` are documented in
this file.

## 1.4.0 - 2026-09-19

A structural cleanup of the templates, the Page TSconfig and the manual. No
public option changed; every view renders as before.

### Fixed

- The example views are appended to `mod.web_list.viewMode.allowed` with
  `:=addToList()` instead of assigning the whole list. Assigning it dropped
  every built-in view that EXT:records_list_types adds after this release.
- `RecordActions.html` and `TranslationStrip.html` of this extension carried
  the names of two EXT:records_list_types partials. Because `partialRootPath`
  is appended after the parent paths, they replaced the parent versions for
  every view, including the built-in ones. They are now
  `RecordCardActions.html` and `RecordCardTranslations.html`, and a unit test
  fails on any future name collision.
- Catalog card titles wrap onto a second line instead of being cut after a few
  words by a single ellipsis line.

### Changed

- Each custom view is one template plus one card partial. Deleted:
  `CatalogRecordCard`, `CatalogRecordList`, `ExpandTableLink`,
  `FirstDisplayValue`, `MultiRecordCheckbox`, `NoRecordsCallout`,
  `RecordListTables`, `RecordTeaser`, `RecordTitleRow`, `TimelineRecordItem`,
  `TimelineRecordList`.
- `Configuration/TsConfig/Page/setup.tsconfig` is gone; the six registrations
  live in `Configuration/page.tsconfig` directly.
- Stylesheets define their colours as `--rle-*` tokens on `.rle-view`, built
  from TYPO3 backend tokens and `light-dark()` instead of duplicated
  colour-scheme blocks.
- `composer.json` relaxes the patch-level pins on `typo3/cms-*`, php-cs-fixer
  and PHPUnit.
- One `.editorconfig` in the repository root instead of one per directory.

### Added

- Unit tests for the stylesheet contract (cache-buster version, token usage,
  styled and rendered classes match), for the manual (README length and
  section order, RST only, release version, no reference to a file that no
  longer exists) and for `composer.json` (extension key, parent constraint,
  and the PSR-4 entry that keeps TYPO3 out of its legacy class scan).
- The view catalogue is back in the README as a table.

## 1.3.0 - 2026-09-12

Alignment with the records_list_types 1.1.0 label catalog and a quality
baseline: static analysis, coding standards and a test suite that renders every
example view.

### Added

- Unit tests for the Page TSconfig presets (every view registers with
  translated labels, an existing template, existing assets and sane paging),
  the label catalog (own, parent and Core keys resolve, the German file mirrors
  the source, templates carry no hard-coded English) and the backend template
  contract (layout-only entry points, existing partials, sanitized backend
  fragments, state-aware accessible names).
- Functional tests that boot EXT:records_list_types together with this
  extension and render all six example views through the Records module
  controller, including hidden-record state for Timeline and Catalog.
- `Build/phpunit/UnitTests.xml` and `Build/phpunit/FunctionalTests.xml` with
  typo3/testing-framework; SQLite locally, MariaDB 10.11 in CI.
- `phpstan.neon` at level 8 with strict rules, phpstan-typo3 and phpstan-phpunit
  and no baseline, plus `.php-cs-fixer.dist.php` with the TYPO3 coding
  standards.
- `Documentation/ExampleViews/Index.rst` with the per-view catalogue and
  `Documentation/Developer/Index.rst` with the template contract, the steps for
  adding a view and the local development commands.

### Changed

- Require `webconsulting/records-list-types` ^1.1, TYPO3 14.3.6+ and PHP 8.4+.
- TSconfig and Fluid address labels through TYPO3 14 translation domains
  (`records_list_examples.messages`, `records_list_types.messages`,
  `core.core`) instead of `LLL:` paths with duplicated `default` texts.
- Action labels come from the parent catalog, so both extensions use one term
  per concept: *Edit record*, *Hide record*, *Unhide record*, *Delete record*,
  *More actions*. Visibility toggles carry state-aware `aria-label`s.
- The translation strip uses ICU placeholders (`translation.translateTo`,
  `translation.edit`, `translation.progress`) and the Core label *No title*
  instead of the sprintf `%s` form and "N/A".
- The own label catalog keeps the six view names, their descriptions and the
  catalog image placeholder; every unit carries a translator note and the
  German targets are marked `final`.
- `Build/Scripts/runTests.sh` offers lint, unit, functional, phpstan, cgl,
  composer, audit and ci suites; a single `.github/workflows/ci.yml` runs them.
- README restructured to What it is, Requirements, Install, Configure, Use,
  Develop, Docs, License; the per-view catalogue moved into the RST manual.
- `composer.json` declares the PSR-4 namespace. Without an `autoload` section
  TYPO3 falls back to scanning the complete extension directory in classic
  mode.

### Removed

- `Build/Scripts/validate-xlf.php`: the unit suite checks the catalogs, and
  `runTests.sh -s lint` checks XLIFF well-formedness.
- The label ids `catalog.badge.hidden`, `noRecords` and the `action.*` set from
  the own catalog; they now resolve from EXT:records_list_types, which also
  ends the use of its deprecated `action.show` alias.
- The `typo3/cms-recordlist` requirement. The package does not exist for v14;
  the Records module lives in `typo3/cms-backend`.

### Security

- No known security issues in this release. TYPO3 Core advisories cannot be
  fixed from this package; CI reports them through `composer audit` without
  blocking the pipeline.

### Known limitations

- TYPO3 14.3.7 added an eleventh constructor argument
  (`RecordIdentityRenderer`) to `TYPO3\CMS\Backend\Controller\RecordListController`.
  EXT:records_list_types 1.1.0 passes ten, so the Records module raises an
  `ArgumentCountError` on 14.3.7 regardless of the selected view. All six
  example views are verified against 14.3.6; using them on 14.3.7 needs a fixed
  EXT:records_list_types release.

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
