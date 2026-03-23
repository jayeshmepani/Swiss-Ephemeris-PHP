<?php

/**
 * Swiss Ephemeris Configuration.
 *
 * Configuration options for Swiss Ephemeris FFI package.
 * Publish to your application with: php artisan vendor:publish --tag=swisseph-config
 *
 * @see https://www.astro.com/swisseph/swephprg.htm Swiss Ephemeris Documentation
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Swiss Ephemeris Library Path
    |--------------------------------------------------------------------------
    |
    | Path to the compiled Swiss Ephemeris shared library (OS-specific).
    | If null, the package will search in common locations:
    | - Package's pre-compiled library (libs/<os-arch>/)
    | - Build directory (build/)
    | - System libraries (/usr/local/lib/, /usr/lib/) on Linux
    |
    | Set this if you have a custom-compiled library.
    |
    */
    'library_path' => env('SWISSEPH_LIBRARY_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Ephemeris Files Path
    |--------------------------------------------------------------------------
    |
    | Path to the directory containing Swiss Ephemeris data files (se1*.eph).
    | These files are required for high-precision JPL ephemeris calculations.
    |
    | Download from: https://github.com/aloistr/swisseph/tree/master/ephe
    |
    | If null, uses Swiss Ephemeris default (current directory).
    |
    */
    'epath' => env('SWISSEPH_EPATH'),

    /*
    |--------------------------------------------------------------------------
    | Default Calculation Flags
    |--------------------------------------------------------------------------
    |
    | Default flags for planetary calculations.
    |
    | Common flags (combine with bitwise OR):
    | - SEFLG_SWIEPH (1): Use Swiss Ephemeris
    | - SEFLG_SPEED (256): Include speed calculations
    | - SEFLG_EQUATORIAL (2048): Return equatorial coordinates
    | - SEFLG_SIDEREAL (65536): Use sidereal zodiac
    |
    | Default: 129 (SWIEPH + SPEED)
    |
    */
    'default_flags' => (int) env('SWISSEPH_DEFAULT_FLAGS', 129), // SWIEPH + SPEED

    /*
    |--------------------------------------------------------------------------
    | Default House System
    |--------------------------------------------------------------------------
    |
    | Default house calculation system for birth chart calculations.
    |
    | Available systems:
    | - P: Placidus (most common)
    | - K: Koch
    | - C: Campanus
    | - R: Regiomontanus
    | - E: Equal (from Ascendant)
    | - T: Polich/Page (topocentric)
    | - B: Alcabitius
    | - M: Morinus
    | - U: Krusinski-Pisa-Goelzer
    |
    */
    'default_house_system' => env('SWISSEPH_HOUSE_SYSTEM', 'P'),

    /*
    |--------------------------------------------------------------------------
    | Sidereal Mode
    |--------------------------------------------------------------------------
    |
    | Default sidereal mode for sidereal zodiac calculations.
    | Set to null for tropical zodiac (default).
    |
    | Common modes:
    | - SE_SIDM_LAHIRI (1): Lahiri (Indian standard)
    | - SE_SIDM_FAGAN_BRADLEY (0): Fagan/Bradley (Western sidereal)
    | - SE_SIDM_DELUCE (2): De Luce
    | - SE_SIDM_RAMAN (3): Raman
    |
    | @see SwissEphFFI constants SE_SIDM_* for full list
    |
    */
    'sidereal_mode' => env('SWISSEPH_SIDEREAL_MODE'),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable logging of Swiss Ephemeris operations.
    | Useful for debugging calculation issues or performance monitoring.
    |
    */
    'logging' => (bool) env('SWISSEPH_LOGGING', false),
];
