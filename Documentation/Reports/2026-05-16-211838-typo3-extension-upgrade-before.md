# TYPO3 Extension Upgrade Report - Before

Date: 2026-05-16 21:18:38 Europe/Vienna

## Scope

Skill: `/typo3-extension-upgrade`

Goal: make `records_list_examples` TYPO3 v14 only and align it with the
current Records List Types v14 extension package.

## Findings

- `composer.json` allows TYPO3 `^14.0`, but the target v14 LTS baseline is
  TYPO3 14.3.
- `ext_emconf.php` has TYPO3 `14.0.0-14.99.99` but no explicit PHP, Backend,
  or Fluid constraints.
- The package version is still `1.0.0` in `ext_emconf.php`; composer metadata
  has no corresponding TYPO3 package version.
- The dependency on `webconsulting/records-list-types` is still `^1.0`, while
  the related v14 package uses version `14.0.0`.
- No PHP classes exist, so Rector-based PHP API migration is not applicable.
- Page TSconfig already uses the TYPO3 API-first auto-inclusion pattern via
  `Configuration/page.tsconfig`.

## Suggested Changes

- Require TYPO3 v14.3+ packages only.
- Require Records List Types v14 only.
- Set extension package metadata to version `14.0.0`.
- Keep the extension PHP-free and avoid inventing replacement runtime code.
