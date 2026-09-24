<?php

declare(strict_types=1);

namespace Webconsulting\RecordsListExamples\Tests\Unit\Template;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webconsulting\RecordsListExamples\Tests\Support\ExtensionPaths;

/**
 * Guards the contract between the Timeline and Catalog templates and the
 * records_list_types rendering pipeline: the documented Records module shell
 * around the record loop, partials that exist and do not shadow parent
 * partials, sanitized backend fragments and accessible names on icon-only
 * actions.
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
    public function viewTemplateRendersTheRecordsModuleShellAroundItsCardPartial(string $template, string $cardPartial): void
    {
        $html = ExtensionPaths::read(self::TEMPLATES . $template . '.html');

        self::assertStringContainsString('<records-list-types-actions>', $html, 'The shared JavaScript needs the action element.');
        self::assertStringContainsString('name="cmd_table"', $html, 'The Core bulk-action form must wrap the records.');
        self::assertStringContainsString('data-multi-record-selection-identifier', $html);
        foreach (['RecordFilters', 'TableHeadingBlock', 'EmptyRecordsNotice'] as $partial) {
            self::assertStringContainsString('partial="' . $partial . '"', $html, $template . ' must render ' . $partial . '.');
        }
        self::assertSame(2, substr_count($html, 'partial="Pagination"'), $template . ' paginates above and below the records.');
        self::assertStringContainsString("position: 'top'", $html);
        self::assertStringContainsString("position: 'bottom'", $html);
        self::assertStringContainsString('core.core:labels.expandTable', $html, $template . ' must link to the single-table view in multi-table mode.');
        self::assertStringContainsString(
            '<f:render partial="' . $cardPartial . '" arguments="{record: record, table: table}" />',
            $html,
            $template . ' renders one ' . $cardPartial . ' per record.',
        );
    }

    #[Test]
    public function everyRenderedPartialExistsInThisExtensionOrInRecordsListTypes(): void
    {
        $parentPartials = ExtensionPaths::package(ExtensionPaths::PARENT_PACKAGE) . self::PARENT_PARTIALS;
        $missing = [];
        foreach ($this->templates() as $relativePath => $template) {
            preg_match_all('/<f:render\b[^>]*\bpartial="([A-Za-z]+)"/', $template, $matches);
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
    public function cardPartialsShareActionsAndTranslationsAndKeepContextualEditing(): void
    {
        foreach (self::CARD_PARTIALS as $partial) {
            $html = ExtensionPaths::read(self::PARTIALS . $partial . '.html');

            self::assertStringContainsString('<f:render partial="RecordCardActions" arguments="{record: record}" />', $html, $partial);
            self::assertStringContainsString('<f:render partial="RecordCardTranslations" arguments="{record: record}" />', $html, $partial);
            self::assertStringContainsString('typo3-backend-contextual-record-edit-trigger', $html, $partial . ' must open records with the Core contextual edit trigger.');
            self::assertStringContainsString('t3js-multi-record-selection-check', $html, $partial . ' must offer the multi-record selection checkbox.');
            self::assertStringContainsString('data-multi-record-selection-element', $html, $partial);
        }

        self::assertStringContainsString(
            '<f:render partial="RecordActionDropdown" arguments="{record: record, buttonClass: \'rle-record-card__action\'}" />',
            ExtensionPaths::read(self::PARTIALS . 'RecordCardActions.html'),
            'The "More actions" dropdown comes from records_list_types.',
        );
    }

    #[Test]
    public function backendFragmentsAreSanitizedInsteadOfPrintedRaw(): void
    {
        foreach ($this->templates() as $relativePath => $template) {
            self::assertStringNotContainsString('f:format.raw', $template, $relativePath . ' must sanitize TYPO3-generated fragments.');
            // Fragments also appear in f:if conditions; only output positions count.
            $output = (string)preg_replace('/\bcondition="[^"]*"/', '', $template);
            preg_match_all('/\{table\.(?:actionButtons\.[a-zA-Z]+|multiRecordSelectionActionsHtml)\}/', $output, $fragments);
            preg_match_all('/<f:sanitize\.html build="records-list-types-backend-fragments">\{table\.(?:actionButtons\.[a-zA-Z]+|multiRecordSelectionActionsHtml)\}<\/f:sanitize\.html>/', $output, $sanitized);
            self::assertCount(count($fragments[0]), $sanitized[0], $relativePath . ': every backend fragment must go through the records-list-types-backend-fragments sanitizer build.');
        }
    }

    #[Test]
    public function visibilityTogglesCarryStateAwareAccessibleNames(): void
    {
        foreach ($this->templates() as $relativePath => $template) {
            self::assertSame(
                substr_count($template, 'data-gridview-action="show"'),
                substr_count($template, 'aria-label="{f:translate(key: \'records_list_types.messages:action.unhide\')}"'),
                $relativePath . ': every unhide toggle needs the "Unhide record" accessible name.',
            );
            self::assertSame(
                substr_count($template, 'data-gridview-action="hide"'),
                substr_count($template, 'aria-label="{f:translate(key: \'records_list_types.messages:action.hide\')}"'),
                $relativePath . ': every hide toggle needs the "Hide record" accessible name.',
            );
        }
    }

    #[Test]
    public function iconOnlyActionsHaveAccessibleNames(): void
    {
        foreach ($this->templates() as $relativePath => $template) {
            preg_match_all('/<(?:button|a|typo3-backend-contextual-record-edit-trigger|typo3-backend-localization-button)\b[^>]*(?:data-gridview-action="(?:delete|info|show|hide)"|popovertarget=)[^>]*>/s', $template, $matches);
            foreach ($matches[0] as $element) {
                if (str_contains($element, 'dropdown-item')) {
                    continue; // menu entries carry visible text
                }
                self::assertMatchesRegularExpression('/\baria-label="\{f:translate\(/', $element, $relativePath . ': icon-only action lacks an accessible name: ' . $element);
            }
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
