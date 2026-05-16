<?php

declare(strict_types=1);

$files = [
    'Resources/Private/Language/locallang.xlf',
    'Resources/Private/Language/de.locallang.xlf',
];

foreach ($files as $file) {
    $document = simplexml_load_file($file);
    if ($document === false) {
        fwrite(STDERR, sprintf("Invalid XLIFF XML: %s\n", $file));
        exit(1);
    }
}

echo "XLIFF validation passed.\n";
