<?php

declare(strict_types=1);

namespace SwissEph\Service;

use Illuminate\Support\ServiceProvider;
use SwissEph\FFI\SwissEphFFI;

/**
 * Laravel Service Provider for Swiss Ephemeris FFI.
 *
 * Registers SwissEphFFI as a singleton in the Laravel service container,
 * ensuring the Swiss Ephemeris library is loaded once and shared across
 * the entire application lifecycle.
 *
 * Features:
 * - Singleton registration (one instance per request)
 * - Automatic configuration merging
 * - Publishable config and library files
 * - Facade alias support
 *
 * @author Jayesh Patel <jayeshmepani777@gmail.com>
 *
 * @see \SwissEph\FFI\SwissEphFFI
 */
final class SwissEphServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     *
     * Merges package configuration and registers SwissEphFFI as singleton.
     * The singleton ensures only one FFI instance exists per request,
     * preventing multiple library loads and memory waste.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/swisseph.php',
            'swisseph'
        );

        $this->app->singleton('swisseph', function ($app) {
            $libraryPath = $app->make('config')->get('swisseph.library_path');
            return new SwissEphFFI($libraryPath);
        });

        $this->app->alias('swisseph', SwissEphFFI::class);
    }

    /**
     * Bootstrap application services.
     *
     * Registers publishable resources:
     * - config/swisseph.php → config_path('swisseph.php')
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/swisseph.php' => config_path('swisseph.php'),
        ], 'swisseph-config');

        $library = $this->findPublishableLibrary();
        if ($library !== null) {
            $this->publishes([
                $library => public_path(basename($library)),
            ], 'swisseph-library');
        }
    }

    /**
     * Get provided services.
     *
     * @return array<int, class-string>
     */
    public function provides(): array
    {
        return ['swisseph', SwissEphFFI::class];
    }

    private function findPublishableLibrary(): ?string
    {
        $family = PHP_OS_FAMILY;
        $arch = strtolower(php_uname('m'));
        $arch = match (true) {
            in_array($arch, ['x86_64', 'amd64'], true) => 'x64',
            in_array($arch, ['aarch64', 'arm64'], true) => 'arm64',
            default => $arch,
        };

        $file = match ($family) {
            'Windows' => 'swe.dll',
            'Darwin' => 'libswe.dylib',
            default => 'libswe.so',
        };

        $candidateDirs = [
            __DIR__ . '/../../libs/' . match ($family) {
                'Windows' => 'windows-' . $arch,
                'Darwin' => 'macos-' . $arch,
                default => 'linux-' . $arch,
            },
            __DIR__ . '/../../build',
        ];

        foreach ($candidateDirs as $dir) {
            $path = $dir . '/' . $file;
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
