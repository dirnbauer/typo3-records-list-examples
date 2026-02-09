<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

ExtensionManagementUtility::addPageTSConfig(
    '@import "EXT:records_list_examples/Configuration/TsConfig/Page/setup.tsconfig"'
);
