<?php

declare(strict_types=1);

/*
 * TYPO3 coding guidelines (typo3/coding-standards) for the extension.
 * Run `Build/Scripts/runTests.sh -s cgl` for a dry run.
 */

use PhpCsFixer\Finder;
use TYPO3\CodingStandards\CsFixerConfig;

$finder = (new Finder())
    ->in(__DIR__ . '/Tests')
    ->append([__FILE__])
    ->name('*.php')
    ->ignoreDotFiles(false)
    ->ignoreVCS(true);

return CsFixerConfig::create()
    ->setFinder($finder);
