# TYPO3 Extension Upgrade Report - After

Date: 2026-05-16 21:18:38 Europe/Vienna

## Scope

Skill: `/typo3-extension-upgrade`

## Changes Made

- Updated extension metadata to version `14.0.0`.
- Restricted Composer TYPO3 dependencies to v14.3+ only.
- Restricted `ext_emconf.php` TYPO3 dependencies to `14.3.0-14.99.99`.
- Added explicit Backend and Fluid package dependencies.
- Updated Records List Types dependency to v14 by aliasing `dev-main` as
  `14.0.0` and using the GitHub VCS repository.
- Removed the unused PSR-4 autoload section for the missing `Classes/`
  directory.

## Verification

- Composer resolved TYPO3 `14.3.1` in a PHP 8.5 container.
- `composer ci` passed in the PHP 8.5 container.
- No Rector changes were needed because the extension has no PHP runtime
  classes.
