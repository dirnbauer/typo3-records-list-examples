<?php

declare(strict_types=1);

const XLIFF_20_NAMESPACE = 'urn:oasis:names:tc:xliff:document:2.0';

/**
 * @return array<string, array{source: string, target?: string}>
 */
function loadUnits(string $file, bool $requiresTarget): array
{
    $document = new DOMDocument();
    $document->preserveWhiteSpace = false;

    if (!$document->load($file)) {
        throw new RuntimeException(sprintf('Invalid XML: %s', $file));
    }

    $root = $document->documentElement;
    if (!$root instanceof DOMElement) {
        throw new RuntimeException(sprintf('Missing XLIFF root element: %s', $file));
    }

    if ($root->namespaceURI !== XLIFF_20_NAMESPACE || $root->getAttribute('version') !== '2.0') {
        throw new RuntimeException(sprintf('Expected XLIFF 2.0 namespace/version in %s', $file));
    }

    $xpath = new DOMXPath($document);
    $xpath->registerNamespace('x', XLIFF_20_NAMESPACE);

    $units = [];
    $unitNodes = $xpath->query('//x:unit');
    if (!$unitNodes instanceof DOMNodeList) {
        throw new RuntimeException(sprintf('Could not read units from %s', $file));
    }

    foreach ($unitNodes as $unit) {
        if (!$unit instanceof DOMElement) {
            continue;
        }

        $id = $unit->getAttribute('id');
        if ($id === '') {
            throw new RuntimeException(sprintf('Unit without id in %s', $file));
        }

        $source = readSegmentText($xpath, $unit, 'source');
        if ($source === '') {
            throw new RuntimeException(sprintf('Missing source for unit "%s" in %s', $id, $file));
        }

        $data = ['source' => $source];

        if ($requiresTarget) {
            $target = readSegmentText($xpath, $unit, 'target');
            if ($target === '') {
                throw new RuntimeException(sprintf('Missing target for unit "%s" in %s', $id, $file));
            }
            $data['target'] = $target;
        }

        $units[$id] = $data;
    }

    if ($units === []) {
        throw new RuntimeException(sprintf('No translation units found in %s', $file));
    }

    return $units;
}

function readSegmentText(DOMXPath $xpath, DOMElement $unit, string $elementName): string
{
    $nodes = $xpath->query('x:segment/x:' . $elementName, $unit);
    if (!$nodes instanceof DOMNodeList || $nodes->length === 0) {
        return '';
    }

    $node = $nodes->item(0);
    if (!$node instanceof DOMNode) {
        return '';
    }

    return trim($node->textContent);
}

try {
    $sourceFile = 'Resources/Private/Language/locallang.xlf';
    $germanFile = 'Resources/Private/Language/de.locallang.xlf';

    $sourceUnits = loadUnits($sourceFile, false);
    $germanUnits = loadUnits($germanFile, true);

    $missingGerman = array_diff_key($sourceUnits, $germanUnits);
    $extraGerman = array_diff_key($germanUnits, $sourceUnits);

    if ($missingGerman !== []) {
        throw new RuntimeException(sprintf(
            'German XLIFF misses units: %s',
            implode(', ', array_keys($missingGerman))
        ));
    }

    if ($extraGerman !== []) {
        throw new RuntimeException(sprintf(
            'German XLIFF contains unknown units: %s',
            implode(', ', array_keys($extraGerman))
        ));
    }

    foreach ($sourceUnits as $id => $sourceUnit) {
        if ($germanUnits[$id]['source'] !== $sourceUnit['source']) {
            throw new RuntimeException(sprintf('Source mismatch for German unit "%s"', $id));
        }
    }

    echo "XLIFF 2.0 validation passed.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
