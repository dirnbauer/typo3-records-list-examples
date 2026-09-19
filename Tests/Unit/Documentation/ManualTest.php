<?php

declare(strict_types=1);

namespace Webconsulting\RecordsListExamples\Tests\Unit\Documentation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webconsulting\RecordsListExamples\Tests\Support\ExtensionPaths;

/**
 * Guards README and the RST manual: they stay short, keep the documented
 * section order, carry the release version and never point at a file or
 * directory that no longer exists.
 */
final class ManualTest extends TestCase
{
    private const int README_MAX_LINES = 120;
    private const array OWN_FILE_EXTENSIONS = ['html', 'css', 'xlf', 'tsconfig'];
    private const array README_SECTIONS = [
        '## What it is',
        '## Requirements',
        '## Install',
        '## Configure',
        '## Use',
        '## Develop',
        '## Docs',
        '## License',
    ];

    #[Test]
    public function readmeStaysShortAndKeepsTheSectionOrder(): void
    {
        $readme = ExtensionPaths::read('README.md');

        self::assertLessThanOrEqual(self::README_MAX_LINES, substr_count($readme, "\n"), 'Anything longer belongs in Documentation/.');
        self::assertSame(
            self::README_SECTIONS,
            array_values(array_filter(
                explode("\n", $readme),
                static fn(string $line): bool => str_starts_with($line, '## '),
            )),
        );
    }

    #[Test]
    public function theManualIsRstOnly(): void
    {
        self::assertSame([], array_keys(ExtensionPaths::files('Documentation', 'md')), 'Documentation/ holds reStructuredText, not Markdown.');
    }

    #[Test]
    public function readmeAndManualDeclareTheReleaseVersion(): void
    {
        $version = ExtensionPaths::composerVersion();
        [$major, $minor] = explode('.', $version);

        self::assertStringContainsString('version="' . $version . '" release="' . $version . '"', ExtensionPaths::read('Documentation/guides.xml'));
        self::assertMatchesRegularExpression('/^:Version:\n {4}' . preg_quote($version, '/') . '$/m', ExtensionPaths::read('Documentation/Index.rst'));
        foreach (['README.md', 'Documentation/Installation/Index.rst'] as $file) {
            self::assertStringContainsString(
                'composer require webconsulting/records-list-examples:^' . $major . '.' . $minor,
                ExtensionPaths::read($file),
                $file . ' must install the current minor release.',
            );
        }
    }

    #[Test]
    public function everyReferencedFileOfThisRepositoryExists(): void
    {
        $missing = [];
        foreach ($this->documents() as $relativePath => $document) {
            foreach ($this->referencedPaths($document) as $reference) {
                $absolute = ExtensionPaths::root() . '/' . $reference;
                if (str_ends_with($reference, '/') ? !is_dir($absolute) : !is_file($absolute)) {
                    $missing[] = $reference . ' (' . $relativePath . ')';
                }
            }
        }

        self::assertSame([], array_values(array_unique($missing)), 'The manual must not point at files this extension no longer ships.');
    }

    /**
     * Repository paths a document links to or names in a :file: role.
     *
     * A reference with a slash is a path from the repository root. A bare
     * name is resolved against the resource files this extension owns, so
     * :file:`CatalogCard.html` is checked too and a leftover name from a
     * deleted file is reported. Everything else -- :file:`GridView`,
     * :file:`ext_emconf.php` -- is prose about foreign or absent files.
     *
     * @return list<string> paths relative to the repository root
     */
    private function referencedPaths(string $document): array
    {
        preg_match_all('/:file:`([^`]+)`|\]\((?!https?:)([^)#]+)\)/', $document, $matches, PREG_SET_ORDER);

        $own = $this->ownFilesByName();
        $paths = [];
        foreach ($matches as $match) {
            $reference = ($match[1] ?? '') !== '' ? $match[1] : ($match[2] ?? '');
            if (str_contains($reference, '/')) {
                $paths[] = $reference;
            } elseif (in_array(pathinfo($reference, PATHINFO_EXTENSION), self::OWN_FILE_EXTENSIONS, true)) {
                $paths[] = $own[$reference] ?? $reference;
            }
        }

        return $paths;
    }

    /**
     * @return array<string, string> file name => path relative to the repository root
     */
    private function ownFilesByName(): array
    {
        $files = [];
        foreach (['Resources/Private', 'Resources/Public', 'Configuration'] as $directory) {
            foreach (self::OWN_FILE_EXTENSIONS as $extension) {
                foreach (array_keys(ExtensionPaths::files($directory, $extension)) as $relativePath) {
                    $files[basename($relativePath)] = $relativePath;
                }
            }
        }

        return $files;
    }

    /**
     * @return array<string, string> relative path => content
     */
    private function documents(): array
    {
        $documents = ['README.md' => ExtensionPaths::read('README.md')];
        foreach (array_keys(ExtensionPaths::files('Documentation', 'rst')) as $relativePath) {
            $documents[$relativePath] = ExtensionPaths::read($relativePath);
        }

        return $documents;
    }
}
