<?php

declare(strict_types=1);

namespace Webconsulting\RecordsListExamples\Tests\Unit\Asset;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webconsulting\RecordsListExamples\Tests\Support\ExtensionPaths;

/**
 * Guards the stylesheets: the shared import is cache-busted with the release
 * version, every rle-* class is both styled and rendered (no dead CSS, no
 * unstyled markup), and colours come from TYPO3 tokens and light-dark()
 * instead of duplicated colour-scheme blocks.
 */
final class StylesheetContractTest extends TestCase
{
    private const string CSS_DIRECTORY = 'Resources/Public/Css';
    private const string SHARED_STYLESHEET = 'Resources/Public/Css/record-card-shared.css';

    /**
     * @return iterable<string, array{string}>
     */
    public static function viewStylesheetProvider(): iterable
    {
        yield 'timeline' => ['Resources/Public/Css/timeline.css'];
        yield 'catalog' => ['Resources/Public/Css/catalog.css'];
    }

    #[Test]
    #[DataProvider('viewStylesheetProvider')]
    public function viewStylesheetImportsTheSharedFileWithTheReleaseVersionAsCacheBuster(string $file): void
    {
        self::assertStringContainsString(
            '@import "./record-card-shared.css?v=' . ExtensionPaths::composerVersion() . '";',
            ExtensionPaths::read($file),
            'TYPO3 busts only the URL of the included file; the import query must be the release version.',
        );
    }

    #[Test]
    #[DataProvider('viewStylesheetProvider')]
    public function viewStylesheetUsesSharedTokensInsteadOfLiteralColours(string $file): void
    {
        self::assertDoesNotMatchRegularExpression('/#[0-9a-f]{3,8}\b/i', ExtensionPaths::read($file), $file . ' must use the --rle-* tokens of record-card-shared.css.');
    }

    #[Test]
    public function sharedStylesheetDefinesTheTokensOnTheViewContainerAndImportsNothing(): void
    {
        $css = ExtensionPaths::read(self::SHARED_STYLESHEET);

        self::assertStringNotContainsString('@import', $css);
        self::assertMatchesRegularExpression('/^\.rle-view \{/m', $css, 'Tokens live on the .rle-view container both views carry.');
    }

    #[Test]
    public function coloursFollowTheBackendSchemeWithoutDuplicatedSchemeBlocks(): void
    {
        foreach (ExtensionPaths::files(self::CSS_DIRECTORY, 'css') as $relativePath => $path) {
            $css = (string)file_get_contents($path);

            self::assertStringNotContainsString('data-color-scheme', $css, $relativePath . ': use TYPO3 tokens or light-dark() instead of scheme selectors.');
            self::assertStringNotContainsString('prefers-color-scheme', $css, $relativePath . ': use TYPO3 tokens or light-dark() instead of media queries.');
        }
    }

    #[Test]
    public function everyStyledClassIsRenderedAndEveryRenderedClassIsStyled(): void
    {
        $styled = $this->classes(ExtensionPaths::files(self::CSS_DIRECTORY, 'css'), '/\.(rle-[a-z0-9_-]+)/');
        $rendered = $this->classes(ExtensionPaths::files('Resources/Private', 'html'), '/\b(rle-[a-z0-9_-]+)/');

        self::assertSame([], array_values(array_diff($styled, $rendered)), 'Stylesheets contain rules for classes no template renders.');

        $unstyled = array_filter(
            $rendered,
            static fn(string $class): bool => !in_array($class, $styled, true)
                && array_filter($styled, static fn(string $styledClass): bool => str_starts_with($styledClass, $class . '__')) === [],
        );
        self::assertSame([], array_values($unstyled), 'Templates render classes without a rule (block roots count as styled when their elements are).');
    }

    /**
     * @param array<string, string> $files relative path => absolute path
     * @return list<string> sorted, unique class names matched by the pattern's first group
     */
    private function classes(array $files, string $pattern): array
    {
        $classes = [];
        foreach ($files as $path) {
            preg_match_all($pattern, (string)file_get_contents($path), $matches);
            $classes = [...$classes, ...$matches[1]];
        }
        $classes = array_values(array_unique($classes));
        sort($classes);

        return $classes;
    }
}
