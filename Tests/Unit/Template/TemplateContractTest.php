<?php

declare(strict_types=1);

namespace Webconsulting\RecordsListExamples\Tests\Unit\Template;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webconsulting\RecordsListExamples\Tests\Support\ExtensionPaths;

/**
 * Guards the contract between the Timeline and Catalog templates and
 * records_list_types: each table is framed by its Table/Section partial,
 * records show the list view's parts through its Record/* partials, and own
 * partials exist without shadowing parent ones.
 */
final class TemplateContractTest extends TestCase
{
    private const string BACKEND_DIRECTORY = 'Resources/Private/Backend';
    private const string TEMPLATES = 'Resources/Private/Backend/Templates/';
    private const string PARTIALS = 'Resources/Private/Backend/Partials/';
    private const string PARENT_PARTIALS = '/Resources/Private/Partials/';
    private const array CARD_PARTIALS = ['TimelineItem', 'CatalogCard'];

    /**
     * @return iterable<string, array{string, string}> view template, card partial
     */
    public static function viewTemplateProvider(): iterable
    {
        yield 'timeline' => ['TimelineView', 'TimelineItem'];
        yield 'catalog' => ['CatalogView', 'CatalogCard'];
    }

    #[Test]
    public function shipsExactlyTheTwoViewTemplates(): void
    {
        self::assertSame(
            [self::TEMPLATES . 'CatalogView.html', self::TEMPLATES . 'TimelineView.html'],
            array_keys(ExtensionPaths::files(self::BACKEND_DIRECTORY . '/Templates', 'html')),
        );
    }

    #[Test]
    #[DataProvider('viewTemplateProvider')]
    public function viewTemplateFramesEveryTableWithTheParentSection(string $template, string $cardPartial): void
    {
        $html = ExtensionPaths::read(self::TEMPLATES . $template . '.html');

        self::assertStringContainsString('<records-list-types-actions', $html, 'The shared JavaScript needs the action element.');
        self::assertMatchesRegularExpression(
            '/<f:render partial="Table\/Section" arguments="\{table: table, currentTable: currentTable[^"]*\}" contentAs="body">/',
            $html,
            $template . ' must let Table/Section render filters, heading, selection bar, pagination and empty state.',
        );
        self::assertStringContainsString('partial="Table/SelectionToggle"', $html, $template . ' must offer Core\'s selection menu.');
        self::assertStringContainsString(
            '<f:render partial="' . $cardPartial . '" arguments="{record: record, table: table}" />',
            $html,
            $template . ' renders one ' . $cardPartial . ' per record.',
        );
    }

    #[Test]
    public function cardPartialsRenderTheRecordPartsOfTheListView(): void
    {
        foreach (self::CARD_PARTIALS as $partial) {
            $html = ExtensionPaths::read(self::PARTIALS . $partial . '.html');

            foreach (['Record/Checkbox', 'Record/Icon', 'Record/Title', 'Record/States', 'Record/Controls', 'TranslationStrip'] as $recordPartial) {
                self::assertStringContainsString('partial="' . $recordPartial . '"', $html, $partial . ' must render ' . $recordPartial . '.');
            }
            self::assertStringContainsString('data-multi-record-selection-element', $html, $partial);
            self::assertMatchesRegularExpression('/aria-labelledby="\{titleId\}"/', $html, $partial . ': the card is named by its title.');
        }
    }

    #[Test]
    public function everyRenderedPartialExistsInThisExtensionOrInRecordsListTypes(): void
    {
        $parentPartials = ExtensionPaths::package(ExtensionPaths::PARENT_PACKAGE) . self::PARENT_PARTIALS;
        $missing = [];
        foreach ($this->templates() as $relativePath => $template) {
            preg_match_all('/<f:render\b[^>]*\bpartial="([A-Za-z\/]+)"/', $template, $matches);
            foreach (array_unique($matches[1]) as $partial) {
                $ownPath = ExtensionPaths::root() . '/' . self::PARTIALS . $partial . '.html';
                if (!is_file($ownPath) && !is_file($parentPartials . $partial . '.html')) {
                    $missing[] = $partial . ' (' . $relativePath . ')';
                }
            }
        }

        self::assertSame([], $missing, 'Partials must exist in this extension or in records_list_types.');
    }

    #[Test]
    public function ownPartialsDoNotShadowRecordsListTypesPartials(): void
    {
        $own = array_map(basename(...), array_keys(ExtensionPaths::files(self::BACKEND_DIRECTORY . '/Partials', 'html')));
        $parentFiles = glob(ExtensionPaths::package(ExtensionPaths::PARENT_PACKAGE) . self::PARENT_PARTIALS . '*.html');
        self::assertIsArray($parentFiles);
        $parent = array_map(basename(...), $parentFiles);

        self::assertNotSame([], $parent, 'records_list_types must ship partials.');
        self::assertSame(
            [],
            array_values(array_intersect($own, $parent)),
            'partialRootPath is appended after the parent paths, so an own partial with a parent name silently replaces it.',
        );
    }

    #[Test]
    public function templatesPrintNothingUnescaped(): void
    {
        foreach ($this->templates() as $relativePath => $template) {
            self::assertStringNotContainsString('f:format.raw', $template, $relativePath . ': Core fragments are printed by the parent partials.');
        }
    }

    /**
     * @return array<string, string> relative path => template content
     */
    private function templates(): array
    {
        return array_map(
            static fn(string $path): string => (string)file_get_contents($path),
            ExtensionPaths::files(self::BACKEND_DIRECTORY, 'html'),
        );
    }
}
