# TYPO3 Testing Report - After

Date: 2026-05-16 21:18:38 Europe/Vienna

## Scope

Skill: `/typo3-testing`

## Changes Made

- Added `Build/Scripts/runTests.sh` with `composerValidate`, `xlf`,
  `phpstan`, and `ci` suites.
- Added `Build/Scripts/validate-xlf.php`.
- Added Composer scripts: `composer check`, `composer phpstan`, and
  `composer ci`.
- Added GitHub Actions CI.

## Verification

- `composer ci` passed in a PHP 8.5 container.
- Local host PHP is 8.1.34, so TYPO3 v14 runtime checks are intentionally run
  in Docker and in GitHub Actions with PHP 8.3.
- PHPUnit was not added because there are no PHP classes to unit test.
