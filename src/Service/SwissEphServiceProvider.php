<?php

declare(strict_types=1);

namespace SwissEph\Service;

use SwissEph\FFI\SwissEphFFI;
use Illuminate\Support\ServiceProvider;

/**
 * Laravel Service Provider for Swiss Ephemeris FFI
 * 
 * Registers the SwissEphFFI as a singleton in the Laravel service container.
 * This ensures the library is loaded once and shared across the application.
 * 
 * @package SwissEph\Service
 */
class SwissEphServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * 
     * @return void
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/swisseph.php',
            'swisseph'
        );
        
        // Register SwissEphFFI as singleton
        $this->app->singleton('swisseph', function ($app) {
            $config = $app->make('config')->get('swisseph');
            
            $libraryPath = $config['library_path'] ?? null;
            
            return new SwissEphFFI($libraryPath);
        });
        
        // Register facade alias
        $this->app->alias('swisseph', SwissEphFFI::class);
    }
    
    /**
     * Bootstrap any application services.
     * 
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../../config/swisseph.php' => config_path('swisseph.php'),
        ], 'swisseph-config');
        
        // Publish library (optional)
        $this->publishes([
            __DIR__ . '/../../build/libswe.so' => public_path('libswe.so'),
        ], 'swisseph-library');
    }
    
    /**
     * Get the services provided by the provider.
     * 
     * @return array<int, class-string>
     */
    public function provides(): array
    {
        return ['swisseph', SwissEphFFI::class];
    }
}
