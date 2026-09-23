<?php

declare(strict_types=1);

namespace Webconsulting\RecordsListExamples\Tests\Unit\Asset;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webconsulting\RecordsListExamples\Tests\Support\ExtensionPaths;

/**
 * Guards the stylesheets: colours come from TYPO3 design tokens only, so the
 * views follow the colour scheme chosen in the backend, and every rle-*
 * class is both styled and rendered (no dead CSS, no unstyled markup).
 */
final class StylesheetContractTest extends TestCase
{
    private const string CSS_DIRECTORY = 'Resources/Public/Css';

    #[Test]
    public function coloursComeFromTypo3TokensOnly(): void
    {
        foreach (ExtensionPaths::files(self::CSS_DIRECTORY, 'css') as $relativePath => $path) {
            $css = (string)preg_replace('#/\*.*?\*/#s', '', (string)file_get_contents($path));

            self::assertDoesNotMatchRegularExpression('/#[0-9a-f]{3,8}\b/i', $css, $relativePath . ' hard-codes a hex colour.');
            self::assertDoesNotMatchRegularExpression('/\b(?:rgba?|hsla?|oklch|lab|lch|light-dark)\(/', $css, $relativePath . ' defines a colour of its own.');
            self::assertStringNotContainsString('--bs-', $css, $relativePath . ' uses Bootstrap variables that do not follow the backend scheme.');
            self::assertStringNotContainsString('prefers-color-scheme', $css, $relativePath . ' queries the OS colour scheme instead of following the backend.');
            self::assertStringNotContainsString('@import', $css, $relativePath . ': TYPO3 cache-busts only the file it includes.');
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
