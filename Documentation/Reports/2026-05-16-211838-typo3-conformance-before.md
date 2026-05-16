# TYPO3 Conformance Report - Before

Date: 2026-05-16 21:18:38 Europe/Vienna

## Scope

Skill: `/typo3-conformance`

## Findings

- Basic extension structure is valid for a backend examples extension.
- No PHP classes exist, so strict types, constructor DI, service registration,
  and prohibited PHP API checks are not applicable.
- `composer.json` still has a PSR-4 autoload section pointing to a missing
  `Classes/` directory.
- No PHPStan max-level config exists in this repository.
- No CI workflow exists in this repository.
- No dedicated TYPO3 documentation tree exists; only `README.md` exists.
- XLIFF files are valid XML but use 4-space indentation instead of the TYPO3
  v14 conformance preference of 2-space XLIFF indentation.

## Suggested Changes

- Remove unused autoload metadata.
- Add PHPStan level `max` and CI checks.
- Add TYPO3 documentation scaffolding.
- Reformat XLIFF files consistently.
