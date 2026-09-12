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
 * Guards the Page TSconfig presets: every example view is registered with
 * translated labels, an existing template, existing assets and sane paging.
 */
final class ViewTypePresetsTest extends TestCase
{
    private const string ENTRY_FILE = 'Configuration/page.tsconfig';
    private const string PRESET_FILE = 'Configuration/TsConfig/Page/setup.tsconfig';
    private const string OWN_DOMAIN = 'records_list_examples.messages';
    private const array BUILTIN_VIEWS = ['list', 'grid', 'compact', 'teaser'];
    private const array CUSTOM_VIEWS = ['timeline', 'catalog'];

    /**
     * @return iterable<string, array{string, string, int}> view id, template, items per page
     */
    public static function exampleViewProvider(): iterable
    {
        yield 'timeline' => ['timeline', 'TimelineView', 50];
        yield 'catalog' => ['catalog', 'CatalogView', 24];
        yield 'addressbook' => ['addressbook', 'CompactView', 500];
        yield 'eventlist' => ['eventlist', 'TeaserView', 30];
        yield 'gallery' => ['gallery', 'GridView', 48];
        yield 'dashboard' => ['dashboard', 'GridView', 20];
    }

    #[Test]
    public function pageTsconfigEntryPointImportsThePresets(): void
    {
        self::assertStringContainsString(
            "@import 'EXT:" . ExtensionPaths::EXTENSION_KEY . '/' . self::PRESET_FILE . "'",
            ExtensionPaths::read(self::ENTRY_FILE),
        );
    }

    #[Test]
    public function presetsUseTheSupportedAllowedViewsOptionOnly(): void
    {
        $tsconfig = ExtensionPaths::read(self::PRESET_FILE);

        self::assertStringNotContainsString('allowedViews', $tsconfig, 'mod.web_list.allowedViews is deprecated since records_list_types 1.1.0.');
        self::assertStringNotContainsString('LLL:', $tsconfig, 'Labels must use the translation domain syntax.');
    }

    #[Test]
    public function allowedViewsListTheBuiltinAndAllExampleViews(): void
    {
        $allowed = GeneralUtility::trimExplode(',', $this->string($this->viewModeConfig()['allowed'] ?? null), true);

        self::assertSame(array_merge(self::BUILTIN_VIEWS, self::exampleViewIds()), $allowed);
    }

    #[Test]
    public function presetsRegisterExactlyTheDocumentedExampleViews(): void
    {
        self::assertSame(self::exampleViewIds(), array_keys($this->types()));
    }

    #[Test]
    #[DataProvider('exampleViewProvider')]
    public function exampleViewIsRegisteredWithLabelsTemplateAndAssets(string $viewId, string $template, int $itemsPerPage): void
    {
        $config = $this->types()[$viewId];

        self::assertSame(self::OWN_DOMAIN . ':viewMode.' . $viewId, $config['label'] ?? null);
        self::assertSame(self::OWN_DOMAIN . ':viewMode.' . $viewId . '.description', $config['description'] ?? null);
        self::assertNotSame('', $this->string($config['icon'] ?? null), $viewId . ' needs an icon identifier.');
        self::assertSame($template, $config['template'] ?? null);
        self::assertSame((string)$itemsPerPage, $config['itemsPerPage'] ?? null);
        self::assertContains($config['columnsFromTCA'] ?? null, ['0', '1'], $viewId . ' must decide between TCA columns and displayColumns.');
        if (($config['columnsFromTCA'] ?? null) === '0') {
            self::assertNotSame('', $this->string($config['displayColumns'] ?? null), $viewId . ' must list displayColumns when columnsFromTCA is off.');
        }

        self::assertFileExists(ExtensionPaths::resolve($this->string($config['css'] ?? null)), $viewId . ': stylesheet missing.');

        $templateRootPath = $this->string($config['templateRootPath'] ?? null);
        $templateDirectory = $templateRootPath === ''
            ? ExtensionPaths::package(ExtensionPaths::PARENT_PACKAGE) . '/Resources/Private/Templates/'
            : ExtensionPaths::resolve($templateRootPath);
        self::assertFileExists($templateDirectory . $template . '.html', $viewId . ': template missing.');

        $partialRootPath = $this->string($config['partialRootPath'] ?? null);
        if ($partialRootPath !== '') {
            self::assertDirectoryExists(ExtensionPaths::resolve($partialRootPath), $viewId . ': partial root missing.');
        }
    }

    #[Test]
    public function customViewsShipTheirOwnTemplateAndPartialRoots(): void
    {
        foreach (self::CUSTOM_VIEWS as $viewId) {
            $config = $this->types()[$viewId];

            self::assertSame('EXT:records_list_examples/Resources/Private/Backend/Templates/', $config['templateRootPath'] ?? null, $viewId);
            self::assertSame('EXT:records_list_examples/Resources/Private/Backend/Partials/', $config['partialRootPath'] ?? null, $viewId);
            self::assertSame('EXT:records_list_examples/Resources/Public/Css/' . $viewId . '.css', $config['css'] ?? null, $viewId);
        }
    }

    #[Test]
    public function reusedBuiltinViewsLoadTheParentStylesheets(): void
    {
        $expected = [
            'addressbook' => 'compact-view.css',
            'eventlist' => 'teaser-view.css',
            'gallery' => 'grid-view.css',
            'dashboard' => 'grid-view.css',
        ];

        foreach ($expected as $viewId => $stylesheet) {
            $config = $this->types()[$viewId];

            self::assertArrayNotHasKey('templateRootPath', $config, $viewId . ' reuses a built-in template and must not override the template root.');
            self::assertArrayNotHasKey('partialRootPath', $config, $viewId . ' reuses built-in partials and must not override the partial root.');
            self::assertSame('EXT:records_list_types/Resources/Public/Css/' . $stylesheet, $config['css'] ?? null, $viewId);
        }
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
     * @return array<mixed> the parsed mod.web_list.viewMode branch
     */
    private function viewModeConfig(): array
    {
        $lineStream = (new LossyTokenizer())->tokenize(ExtensionPaths::read(self::PRESET_FILE));
        $ast = (new AstBuilder(new NoopEventDispatcher()))->build($lineStream, new RootNode());

        $tsConfig = $ast->toArray();
        self::assertIsArray($tsConfig, 'The preset file must parse into a TSconfig tree.');
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
