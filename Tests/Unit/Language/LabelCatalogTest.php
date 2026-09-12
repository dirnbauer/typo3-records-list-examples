<?php

declare(strict_types=1);

namespace Webconsulting\RecordsListExamples\Tests\Unit\Language;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webconsulting\RecordsListExamples\Tests\Support\ExtensionPaths;

/**
 * Guards the label catalog: every referenced key exists (own, parent and Core
 * domains), deprecated parent aliases are not used, the German file mirrors
 * the source, and templates never fall back to hard-coded English.
 */
final class LabelCatalogTest extends TestCase
{
    private const string XLIFF_20_NAMESPACE = 'urn:oasis:names:tc:xliff:document:2.0';
    private const string XLIFF_12_NAMESPACE = 'urn:oasis:names:tc:xliff:document:1.2';
    private const string OWN_DOMAIN = 'records_list_examples.messages';
    private const string PARENT_DOMAIN = 'records_list_types.messages';
    private const string SOURCE_FILE = 'Resources/Private/Language/locallang.xlf';
    private const string GERMAN_FILE = 'Resources/Private/Language/de.locallang.xlf';

    /** Core translation domains and the files they map to (TranslationDomainMapper). */
    private const array CORE_DOMAIN_FILES = [
        'core.core' => 'Resources/Private/Language/locallang_core.xlf',
        'core.common' => 'Resources/Private/Language/locallang_common.xlf',
        'core.mod_web_list' => 'Resources/Private/Language/locallang_mod_web_list.xlf',
    ];

    /**
     * @return iterable<string, array{string}>
     */
    public static function catalogFileProvider(): iterable
    {
        yield 'en' => [self::SOURCE_FILE];
        yield 'de' => [self::GERMAN_FILE];
    }

    #[Test]
    public function sourceCatalogIsAnXliff2TemplateWithOneFileElement(): void
    {
        $xml = $this->loadXml(self::SOURCE_FILE);

        self::assertSame('2.0', (string)$xml['version']);
        self::assertSame('en', (string)$xml['srcLang']);
        self::assertNull($xml['trgLang'] ?? null, 'The source catalog must not declare a target language.');
        self::assertCount(1, $this->query($xml, '/x:xliff/x:file'));
    }

    #[Test]
    public function germanCatalogMirrorsTheSourceUnitsWithReviewedTargets(): void
    {
        $sourceUnits = $this->loadUnits(self::SOURCE_FILE);
        $germanUnits = $this->loadUnits(self::GERMAN_FILE);

        self::assertSame('de', (string)$this->loadXml(self::GERMAN_FILE)['trgLang']);
        self::assertSame(array_keys($sourceUnits), array_keys($germanUnits), 'de.locallang.xlf must contain the same unit ids in the same order as the source.');
        foreach ($germanUnits as $id => $unit) {
            self::assertSame($sourceUnits[$id]['source'], $unit['source'], 'Source text of "' . $id . '" differs from locallang.xlf.');
            self::assertNotSame('', $unit['target'], 'Unit "' . $id . '" has no German target.');
            self::assertSame('final', $unit['state'], 'German unit "' . $id . '" is reviewed and must carry state="final".');
        }
    }

    #[Test]
    #[DataProvider('catalogFileProvider')]
    public function everyUnitCarriesATranslatorNote(string $file): void
    {
        foreach ($this->loadUnits($file) as $id => $unit) {
            self::assertNotSame('', trim($unit['note']), $file . ': unit "' . $id . '" has no <note>.');
        }
    }

    #[Test]
    #[DataProvider('catalogFileProvider')]
    public function catalogUsesIcuPlaceholdersOnly(string $file): void
    {
        foreach ($this->loadUnits($file) as $id => $unit) {
            foreach ([$unit['source'], $unit['target']] as $text) {
                self::assertDoesNotMatchRegularExpression('/%(?:\d+\$)?[sd]/', $text, $file . ': unit "' . $id . '" uses sprintf placeholders.');
            }
        }
    }

    #[Test]
    public function sourceLabelsUseSentenceCaseInsteadOfAllCaps(): void
    {
        foreach ($this->loadUnits(self::SOURCE_FILE) as $id => $unit) {
            self::assertDoesNotMatchRegularExpression('/\b[A-Z]{3,}\b/', $unit['source'], 'Unit "' . $id . '" shouts in all caps.');
        }
    }

    #[Test]
    public function everyReferencedOwnKeyExistsInTheCatalog(): void
    {
        $units = $this->loadUnits(self::SOURCE_FILE);
        $missing = [];
        foreach ($this->collectReferencedKeys(self::OWN_DOMAIN) as $key => $locations) {
            if (!isset($units[$key])) {
                $missing[] = $key . ' (' . implode(', ', $locations) . ')';
            }
        }

        self::assertSame([], $missing, 'Referenced label keys are missing from locallang.xlf.');
    }

    #[Test]
    public function everyCatalogUnitIsReferenced(): void
    {
        $referenced = $this->collectReferencedKeys(self::OWN_DOMAIN);
        $unused = array_values(array_filter(
            array_keys($this->loadUnits(self::SOURCE_FILE)),
            static fn(string $id): bool => !isset($referenced[$id]),
        ));

        self::assertSame([], $unused, 'locallang.xlf contains units that no template or TSconfig references.');
    }

    #[Test]
    public function everyReferencedParentKeyExistsAndIsNotDeprecated(): void
    {
        $parentUnits = $this->loadUnits(self::SOURCE_FILE, ExtensionPaths::package(ExtensionPaths::PARENT_PACKAGE));
        $problems = [];
        foreach ($this->collectReferencedKeys(self::PARENT_DOMAIN) as $key => $locations) {
            if (!isset($parentUnits[$key])) {
                $problems[] = $key . ' is missing from the records_list_types catalog (' . implode(', ', $locations) . ')';
            } elseif ($parentUnits[$key]['deprecated']) {
                $problems[] = $key . ' is a deprecated records_list_types alias (' . implode(', ', $locations) . ')';
            }
        }

        self::assertSame([], $problems);
    }

    #[Test]
    public function everyReferencedCoreKeyExists(): void
    {
        $coreRoot = ExtensionPaths::package(ExtensionPaths::CORE_PACKAGE);
        $problems = [];
        foreach ($this->collectCoreReferences() as $domain => $keys) {
            $file = self::CORE_DOMAIN_FILES[$domain] ?? null;
            if ($file === null) {
                $problems[] = 'Unknown Core domain "' . $domain . '" (' . implode(', ', array_keys($keys)) . ')';
                continue;
            }
            $ids = $this->loadXliff12Ids($coreRoot . '/' . $file);
            foreach ($keys as $key => $locations) {
                if (!in_array($key, $ids, true)) {
                    $problems[] = $domain . ':' . $key . ' does not exist in ' . $file . ' (' . implode(', ', $locations) . ')';
                }
            }
        }

        self::assertSame([], $problems);
    }

    #[Test]
    public function templatesContainNoHardCodedEnglish(): void
    {
        foreach (ExtensionPaths::files('Resources/Private', 'html') as $relativePath => $path) {
            $template = (string)file_get_contents($path);

            self::assertStringNotContainsString('LLL:EXT:', $template, $relativePath . ' must reference labels through translation domains.');
            self::assertStringNotContainsString('extensionName:', $template, $relativePath . ' must reference labels through translation domains.');
            self::assertDoesNotMatchRegularExpression('/<f:translate\b[^>]*\bdefault="/', $template, $relativePath . ' duplicates label text in a default attribute.');
            self::assertDoesNotMatchRegularExpression('/f:translate\([^)]*\bdefault:/', $template, $relativePath . ' duplicates label text in a default argument.');
            self::assertDoesNotMatchRegularExpression('/\b(?:title|aria-label)="[^"{]*[A-Za-z][^"{]*"/', $template, $relativePath . ' has an untranslated title or aria-label literal.');
            self::assertDoesNotMatchRegularExpression('/<em>\s*[A-Za-z][^<{]*<\/em>/', $template, $relativePath . ' has an untranslated <em> literal.');
        }
    }

    /**
     * @return array<string, list<string>> key => locations
     */
    private function collectReferencedKeys(string $domain): array
    {
        $pattern = '/' . preg_quote($domain, '/') . ':([A-Za-z0-9_.]+)/';
        $referenced = [];
        foreach ($this->sourceFiles() as $relativePath => $path) {
            preg_match_all($pattern, (string)file_get_contents($path), $matches);
            foreach ($matches[1] as $key) {
                $referenced[$key][] = $relativePath;
            }
        }

        return array_map(static fn(array $locations): array => array_values(array_unique($locations)), $referenced);
    }

    /**
     * @return array<string, array<string, list<string>>> domain => key => locations
     */
    private function collectCoreReferences(): array
    {
        $referenced = [];
        foreach ($this->sourceFiles() as $relativePath => $path) {
            preg_match_all('/\b(core\.[a-z_]+):([A-Za-z0-9_.]+)/', (string)file_get_contents($path), $matches, PREG_SET_ORDER);
            foreach ($matches as [, $domain, $key]) {
                $referenced[$domain][$key][] = $relativePath;
            }
        }

        return $referenced;
    }

    /**
     * @return array<string, string> relative path => absolute path
     */
    private function sourceFiles(): array
    {
        return array_merge(
            ExtensionPaths::files('Resources/Private', 'html'),
            ExtensionPaths::files('Configuration', 'tsconfig'),
        );
    }

    /**
     * @return array<string, array{source: string, target: string, note: string, state: string, deprecated: bool}>
     */
    private function loadUnits(string $file, ?string $root = null): array
    {
        $xml = $this->loadXml($file, $root);
        $units = [];
        foreach ($this->query($xml, '//x:unit') as $unit) {
            $unit->registerXPathNamespace('x', self::XLIFF_20_NAMESPACE);
            $segment = $this->query($unit, './x:segment')[0] ?? null;
            if (!$segment instanceof \SimpleXMLElement) {
                throw new \RuntimeException('Unit without segment in ' . $file, 1757900003);
            }
            $segment->registerXPathNamespace('x', self::XLIFF_20_NAMESPACE);
            $units[(string)$unit['id']] = [
                'source' => $this->text($this->query($segment, './x:source')),
                'target' => $this->text($this->query($segment, './x:target')),
                'note' => $this->text($this->query($unit, './x:notes/x:note')),
                'state' => (string)($segment['state'] ?? ''),
                'deprecated' => (string)($segment['subState'] ?? '') === 'deprecated',
            ];
        }

        return $units;
    }

    /**
     * @return list<string> trans-unit ids of a Core XLIFF 1.2 file
     */
    private function loadXliff12Ids(string $path): array
    {
        $xml = simplexml_load_file($path);
        if ($xml === false) {
            throw new \RuntimeException('Unable to parse ' . $path, 1757900004);
        }
        $xml->registerXPathNamespace('x', self::XLIFF_12_NAMESPACE);

        return array_values(array_map(
            static fn(\SimpleXMLElement $unit): string => (string)$unit['id'],
            $this->query($xml, '//x:trans-unit'),
        ));
    }

    /**
     * @return list<\SimpleXMLElement>
     */
    private function query(\SimpleXMLElement $xml, string $expression): array
    {
        $nodes = $xml->xpath($expression);

        return is_array($nodes) ? array_values($nodes) : [];
    }

    /**
     * @param list<\SimpleXMLElement> $nodes
     */
    private function text(array $nodes): string
    {
        return isset($nodes[0]) ? (string)$nodes[0] : '';
    }

    private function loadXml(string $file, ?string $root = null): \SimpleXMLElement
    {
        $xml = simplexml_load_file(($root ?? ExtensionPaths::root()) . '/' . $file);
        if ($xml === false) {
            throw new \RuntimeException('Unable to parse ' . $file, 1757900005);
        }
        $xml->registerXPathNamespace('x', self::XLIFF_20_NAMESPACE);

        return $xml;
    }
}
