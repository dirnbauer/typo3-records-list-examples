<?php

declare(strict_types=1);

namespace Webconsulting\RecordsListExamples\Tests\Support;

use Composer\InstalledVersions;

/**
 * Resolves files of this extension, of EXT:records_list_types in vendor/ and
 * of TYPO3 Core for tests that read templates, labels, TSconfig and
 * stylesheets from disk.
 */
final class ExtensionPaths
{
    public const string EXTENSION_KEY = 'records_list_examples';
    public const string PARENT_EXTENSION_KEY = 'records_list_types';
    public const string PARENT_PACKAGE = 'webconsulting/records-list-types';
    public const string CORE_PACKAGE = 'typo3/cms-core';

    public static function root(): string
    {
        return dirname(__DIR__, 2);
    }

    public static function package(string $packageName): string
    {
        $path = InstalledVersions::getInstallPath($packageName);
        $realPath = $path === null ? false : realpath($path);
        if ($realPath === false) {
            throw new \RuntimeException('Package "' . $packageName . '" is not installed.', 1757900001);
        }

        return $realPath;
    }

    /**
     * Resolves an EXT:records_list_examples/... or EXT:records_list_types/... path.
     */
    public static function resolve(string $extPath): string
    {
        $ownPrefix = 'EXT:' . self::EXTENSION_KEY . '/';
        if (str_starts_with($extPath, $ownPrefix)) {
            return self::root() . '/' . substr($extPath, strlen($ownPrefix));
        }
        $parentPrefix = 'EXT:' . self::PARENT_EXTENSION_KEY . '/';
        if (str_starts_with($extPath, $parentPrefix)) {
            return self::package(self::PARENT_PACKAGE) . '/' . substr($extPath, strlen($parentPrefix));
        }

        throw new \InvalidArgumentException('Unsupported extension path "' . $extPath . '".', 1757900002);
    }

    /**
     * @return array<string, string> relative path => absolute path, sorted by relative path
     */
    public static function files(string $directory, string $extension): array
    {
        $base = self::root() . '/' . $directory;
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            assert($file instanceof \SplFileInfo);
            if ($file->getExtension() === $extension) {
                $files[$directory . '/' . str_replace($base . '/', '', $file->getPathname())] = $file->getPathname();
            }
        }
        ksort($files);

        return $files;
    }

    public static function read(string $relativePath): string
    {
        return (string)file_get_contents(self::root() . '/' . $relativePath);
    }

    /**
     * The release version declared in composer.json (extra.typo3/cms.version).
     */
    public static function composerVersion(): string
    {
        $composer = json_decode(self::read('composer.json'), true, 512, JSON_THROW_ON_ERROR);
        $extra = is_array($composer) ? ($composer['extra'] ?? null) : null;
        $typo3 = is_array($extra) ? ($extra['typo3/cms'] ?? null) : null;
        $version = is_array($typo3) ? ($typo3['version'] ?? null) : null;
        if (!is_string($version) || $version === '') {
            throw new \RuntimeException('composer.json declares no extra.typo3/cms.version.', 1758200001);
        }

        return $version;
    }

    /**
     * @return list<string> icon identifiers TYPO3 Core registers from its icons.json
     */
    public static function coreIconIdentifiers(): array
    {
        $file = self::package(self::CORE_PACKAGE) . '/Resources/Public/Icons/T3Icons/icons.json';
        $registry = json_decode((string)file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        $icons = is_array($registry) ? ($registry['icons'] ?? null) : null;

        return is_array($icons) ? array_map(strval(...), array_keys($icons)) : [];
    }
}
