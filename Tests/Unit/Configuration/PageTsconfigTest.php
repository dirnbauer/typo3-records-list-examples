<?php

declare(strict_types=1);

namespace Webconsulting\RecordsListExamples\Tests\Unit\Configuration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;
use TYPO3\CMS\Core\TypoScript\AST\AstBuilder;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\Tokenizer\LossyTokenizer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Webconsulting\RecordsListExamples\Tests\Support\ExtensionPaths;

/**
 * Guards Configuration/page.tsconfig: every example view is registered with
 * translated labels, a Core icon, an existing template and stylesheet and
 * a deliberate column configuration.
 */
final class PageTsconfigTest extends TestCase
{
    private const string TSCONFIG_FILE = 'Configuration/page.tsconfig';
    private const string OWN_DOMAIN = 'records_list_examples.messages';
    private const array BUILTIN_VIEWS = ['list', 'grid', 'compact', 'teaser'];
    private const array OWN_TEMPLATE_VIEWS = ['timeline', 'catalog'];

    /**
     * @return iterable<string, array{string, string, string, int}> view id, icon, template, records per page
     */
    public static function exampleViewProvider(): iterable
    {
        yield 'timeline' => ['timeline', 'content-timeline', 'TimelineView', 50];
        yield 'catalog' => ['catalog', 'actions-viewmode-photos', 'CatalogView', 24];
        yield 'addressbook' => ['addressbook', 'actions-users', 'CompactView', 500];
        yield 'eventlist' => ['eventlist', 'actions-calendar', 'TeaserView', 30];
        yield 'gallery' => ['gallery', 'content-gallery', 'GridView', 48];
        yield 'dashboard' => ['dashboard', 'content-dashboard', 'GridView', 20];
    }

    #[Test]
    public function exampleViewsAreAppendedToTheAllowedViewsOfRecordsListTypes(): void
    {
        $allowed = GeneralUtility::trimExplode(',', $this->string($this->viewModeConfig()['allowed'] ?? null), true);

        self::assertSame(self::exampleViewIds(), $allowed, 'addToList() must contribute the example views only.');
        self::assertStringContainsString(
            'allowed := addToList(' . implode(',', self::exampleViewIds()) . ')',
            ExtensionPaths::read(self::TSCONFIG_FILE),
            'Assigning the list would drop built-in views that records_list_types adds later.',
        );
    }

    #[Test]
    public function allowedViewsAppendedOnTopOfTheParentListYieldEveryView(): void
    {
        $parentAllowed = 'mod.web_list.viewMode.allowed = ' . implode(',', self::BUILTIN_VIEWS) . "\n";
        $allowed = $this->viewModeConfig($parentAllowed)['allowed'] ?? null;

        self::assertSame(implode(',', array_merge(self::BUILTIN_VIEWS, self::exampleViewIds())), $allowed);
    }

    #[Test]
    public function registersExactlyTheSixExampleViews(): void
    {
        self::assertSame(self::exampleViewIds(), array_keys($this->types()));
    }

    #[Test]
    #[DataProvider('exampleViewProvider')]
    public function exampleViewIsRegisteredWithLabelsIconTemplateAndPaging(string $viewId, string $icon, string $template, int $itemsPerPage): void
    {
        $config = $this->types()[$viewId];

        self::assertSame(self::OWN_DOMAIN . ':viewMode.' . $viewId, $config['label'] ?? null);
        self::assertSame(self::OWN_DOMAIN . ':viewMode.' . $viewId . '.description', $config['description'] ?? null);
        self::assertSame($icon, $config['icon'] ?? null);
        self::assertContains($icon, ExtensionPaths::coreIconIdentifiers(), $viewId . ': "' . $icon . '" is not a TYPO3 Core icon.');
        self::assertSame($template, $config['template'] ?? null);
        self::assertSame((string)$itemsPerPage, $config['itemsPerPage'] ?? null);

        $columnsFromTca = $config['columnsFromTCA'] ?? null;
        self::assertContains($columnsFromTca, ['0', '1'], $viewId . ' must decide between TCA columns and displayColumns.');
        if ($columnsFromTca === '0') {
            self::assertNotSame('', $this->string($config['displayColumns'] ?? null), $viewId . ' must list displayColumns when columnsFromTCA is off.');
        } else {
            self::assertArrayNotHasKey('displayColumns', $config, $viewId . ': displayColumns is ignored while columnsFromTCA is on.');
        }

        self::assertFileExists(ExtensionPaths::resolve($this->string($config['css'] ?? null)), $viewId . ': stylesheet missing.');

        $templateRootPath = $this->string($config['templateRootPath'] ?? null);
        $templateDirectory = $templateRootPath === ''
            ? ExtensionPaths::package(ExtensionPaths::PARENT_PACKAGE) . '/Resources/Private/Templates/'
            : ExtensionPaths::resolve($templateRootPath);
        self::assertFileExists($templateDirectory . $template . '.html', $viewId . ': template missing.');
    }

    #[Test]
    public function viewsWithOwnTemplatePointToOwnTemplatePartialAndStylesheetPaths(): void
    {
        foreach (self::OWN_TEMPLATE_VIEWS as $viewId) {
            $config = $this->types()[$viewId];

            self::assertSame('EXT:records_list_examples/Resources/Private/Backend/Templates/', $config['templateRootPath'] ?? null, $viewId);
            self::assertSame('EXT:records_list_examples/Resources/Private/Backend/Partials/', $config['partialRootPath'] ?? null, $viewId);
            self::assertSame('EXT:records_list_examples/Resources/Public/Css/' . $viewId . '.css', $config['css'] ?? null, $viewId);
        }
    }

    #[Test]
    public function viewsReusingBuiltinTemplatesOnlyNameTheMatchingParentStylesheet(): void
    {
        foreach (array_diff(self::exampleViewIds(), self::OWN_TEMPLATE_VIEWS) as $viewId) {
            $config = $this->types()[$viewId];
            $stylesheet = strtolower((string)preg_replace('/View$/', '-view', $this->string($config['template'] ?? null))) . '.css';

            self::assertArrayNotHasKey('templateRootPath', $config, $viewId . ' reuses a built-in template and must not override the template root.');
            self::assertArrayNotHasKey('partialRootPath', $config, $viewId . ' reuses built-in partials and must not override the partial root.');
            self::assertSame('EXT:records_list_types/Resources/Public/Css/' . $stylesheet, $config['css'] ?? null, $viewId);
        }
    }

    #[Test]
    public function usesTranslationDomainsAndTheCurrentAllowedOption(): void
    {
        $tsconfig = ExtensionPaths::read(self::TSCONFIG_FILE);

        self::assertStringNotContainsString('allowedViews', $tsconfig, 'mod.web_list.allowedViews is deprecated since records_list_types 1.1.0.');
        self::assertStringNotContainsString('LLL:', $tsconfig, 'Labels must use the translation domain syntax.');
    }

    /**
     * @return list<string>
     */
    private static function exampleViewIds(): array
    {
        return array_keys(iterator_to_array(self::exampleViewProvider()));
    }

    /**
     * @return array<string, array<string, mixed>> view id => options without trailing dots
     */
    private function types(): array
    {
        $rawTypes = $this->viewModeConfig()['types.'] ?? null;
        self::assertIsArray($rawTypes);

        $types = [];
        foreach ($rawTypes as $key => $config) {
            if (!is_string($key) || !is_array($config)) {
                continue;
            }
            $options = [];
            foreach ($config as $optionKey => $value) {
                $options[(string)$optionKey] = $value;
            }
            $types[rtrim($key, '.')] = $options;
        }

        return $types;
    }

    /**
     * Parses the shipped TSconfig, optionally on top of TSconfig that
     * records_list_types contributes before it.
     *
     * @return array<mixed> the parsed mod.web_list.viewMode branch
     */
    private function viewModeConfig(string $parentTsConfig = ''): array
    {
        $tokenizer = new LossyTokenizer();
        $builder = new AstBuilder(new NoopEventDispatcher());
        $ast = $builder->build($tokenizer->tokenize($parentTsConfig), new RootNode());
        $ast = $builder->build($tokenizer->tokenize(ExtensionPaths::read(self::TSCONFIG_FILE)), $ast);

        $tsConfig = $ast->toArray();
        self::assertIsArray($tsConfig, 'The TSconfig file must parse into a tree.');
        $mod = $tsConfig['mod.'] ?? null;
        self::assertIsArray($mod);
        $webList = $mod['web_list.'] ?? null;
        self::assertIsArray($webList);
        $viewMode = $webList['viewMode.'] ?? null;
        self::assertIsArray($viewMode);

        return $viewMode;
    }

    private function string(mixed $value): string
    {
        return is_scalar($value) ? (string)$value : '';
    }
}
