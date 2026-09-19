<?php

declare(strict_types=1);

namespace Webconsulting\RecordsListExamples\Tests\Unit\Package;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webconsulting\RecordsListExamples\Tests\Support\ExtensionPaths;

/**
 * Guards composer.json: the extension key, the dependency on
 * records_list_types and the autoload section TYPO3 needs.
 */
final class ComposerManifestTest extends TestCase
{
    #[Test]
    public function declaresTheExtensionKeyAndTheParentExtension(): void
    {
        $manifest = $this->manifest();

        self::assertSame('typo3-cms-extension', $manifest['type'] ?? null);
        self::assertSame(ExtensionPaths::EXTENSION_KEY, $this->option($manifest, 'extra', 'typo3/cms', 'extension-key'));
        self::assertSame('^1.1', $this->option($manifest, 'require', ExtensionPaths::PARENT_PACKAGE));
    }

    #[Test]
    public function keepsThePsr4AutoloadEntryEvenWithoutRuntimeClasses(): void
    {
        self::assertSame(
            ['Webconsulting\\RecordsListExamples\\' => 'Classes/'],
            $this->option($this->manifest(), 'autoload', 'psr-4'),
            'Without an autoload section TYPO3 scans the whole extension directory for class files.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function manifest(): array
    {
        $manifest = json_decode(ExtensionPaths::read('composer.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($manifest);

        $typed = [];
        foreach ($manifest as $key => $value) {
            $typed[(string)$key] = $value;
        }

        return $typed;
    }

    /**
     * @param array<string, mixed> $manifest
     */
    private function option(array $manifest, string ...$keys): mixed
    {
        $value = $manifest;
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }
}
