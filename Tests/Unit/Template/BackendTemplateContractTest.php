<?php

declare(strict_types=1);

namespace Webconsulting\RecordsListExamples\Tests\Unit\Template;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webconsulting\RecordsListExamples\Tests\Support\ExtensionPaths;

/**
 * Guards the contract between the custom Timeline and Catalog templates and
 * the records_list_types rendering pipeline: layout-only entry points, shared
 * partials that still exist, sanitized backend fragments and accessible names.
 */
final class BackendTemplateContractTest extends TestCase
{
    private const string TEMPLATE_DIRECTORY = 'Resources/Private/Backend';
    private const string OWN_PARTIALS = 'Resources/Private/Backend/Partials/';
    private const string PARENT_PARTIALS = '/Resources/Private/Partials/';

    #[Test]
    public function backendTemplatesDoNotUseFrontendContentAreaRendering(): void
    {
        foreach ($this->templates() as $relativePath => $template) {
            self::assertStringNotContainsString('f:render.contentArea', $template, $relativePath);
            self::assertStringNotContainsString('f:mark.contentArea', $template, $relativePath);
            self::assertStringNotContainsString('lib.dynamicContent', $template, $relativePath);
            self::assertStringNotContainsString('v:content.render', $template, $relativePath);
            self::assertStringNotContainsString('flux:content.render', $template, $relativePath);
        }
    }

    #[Test]
    public function viewTemplatesAreLayoutOnlyEntryPoints(): void
    {
        $viewTemplates = array_filter(
            $this->templates(),
            static fn(string $relativePath): bool => str_starts_with($relativePath, self::TEMPLATE_DIRECTORY . '/Templates/'),
            ARRAY_FILTER_USE_KEY,
        );
        self::assertSame([
            self::TEMPLATE_DIRECTORY . '/Templates/CatalogView.html',
            self::TEMPLATE_DIRECTORY . '/Templates/TimelineView.html',
        ], array_keys($viewTemplates));

        foreach ($viewTemplates as $relativePath => $template) {
            self::assertStringContainsString('<records-list-types-actions>', $template, $relativePath . ' must wrap its markup in the records_list_types action element.');
            self::assertStringContainsString('partial="RecordListTables"', $template, $relativePath . ' must render the shared table shell.');
            preg_match('/recordListPartial: \'([A-Za-z]+)\'/', $template, $matches);
            $recordListPartial = $matches[1] ?? '';
            self::assertNotSame('', $recordListPartial, $relativePath . ' must name its record-list partial.');
            self::assertFileExists(ExtensionPaths::root() . '/' . self::OWN_PARTIALS . $recordListPartial . '.html', $relativePath . ' references a missing record-list partial.');
        }
    }

    #[Test]
    public function renderedPartialsExistLocallyOrInTheParentExtension(): void
    {
        $parentPartials = ExtensionPaths::package(ExtensionPaths::PARENT_PACKAGE) . self::PARENT_PARTIALS;
        $missing = [];
        foreach ($this->templates() as $relativePath => $template) {
            preg_match_all('/<f:render\b[^>]*\bpartial="([A-Za-z]+)"/', $template, $matches);
            foreach (array_unique($matches[1]) as $partial) {
                $ownPath = ExtensionPaths::root() . '/' . self::OWN_PARTIALS . $partial . '.html';
                if (!is_file($ownPath) && !is_file($parentPartials . $partial . '.html')) {
                    $missing[] = $partial . ' (' . $relativePath . ')';
                }
            }
        }

        self::assertSame([], $missing, 'Partials must exist in this extension or in records_list_types.');
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
    public function recordPartialsKeepContextualEditTriggers(): void
    {
        foreach (['CatalogRecordCard', 'RecordActions', 'RecordTitleRow', 'TranslationStrip'] as $partial) {
            $template = ExtensionPaths::read(self::OWN_PARTIALS . $partial . '.html');

            self::assertStringContainsString(
                'typo3-backend-contextual-record-edit-trigger',
                $template,
                $partial . ' must keep backend contextual editing instead of frontend Visual Editor markers.',
            );
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
            self::assertStringNotContainsString('(currently', $template, $relativePath . ' must not append the state in parentheses.');
        }
    }

    #[Test]
    public function iconOnlyRecordActionsHaveAccessibleNames(): void
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
            ExtensionPaths::files(self::TEMPLATE_DIRECTORY, 'html'),
        );
    }
}
