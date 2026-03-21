<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Swiss Ephemeris Library Path
    |--------------------------------------------------------------------------
    |
    | Path to the compiled Swiss Ephemeris shared library (libswe.so).
    | If null, the package will search in common locations.
    |
    */
    'library_path' => env('SWISSEPH_LIBRARY_PATH', null),
    
    /*
    |--------------------------------------------------------------------------
    | Ephemeris Files Path
    |--------------------------------------------------------------------------
    |
    | Path to the directory containing Swiss Ephemeris data files (se1*.eph).
    | Default is to use the system default or current directory.
    |
    */
    'epath' => env('SWISSEPH_EPATH', null),
    
    /*
    |--------------------------------------------------------------------------
    | Default Calculation Flags
    |--------------------------------------------------------------------------
    |
    | Default flags for planetary calculations.
    | Common flags:
    | - SEFLG_SWIEPH (1): Use Swiss Ephemeris
    | - SEFLG_SPEED (128): Include speed calculations
    | - SEFLG_EQUATORIAL (1024): Return equatorial coordinates
    |
    */
    'default_flags' => env('SWISSEPH_DEFAULT_FLAGS', 129), // SWIEPH + SPEED
    
    /*
    |--------------------------------------------------------------------------
    | Default House System
    |--------------------------------------------------------------------------
    |
    | Default house calculation system.
    | Options: P (Placidus), K (Koch), C (Campanus), R (Regiomontanus),
    |          E (Equal), T (Polich/Page), etc.
    |
    */
    'default_house_system' => env('SWISSEPH_HOUSE_SYSTEM', 'P'),
    
    /*
    |--------------------------------------------------------------------------
    | Sidereal Mode
    |--------------------------------------------------------------------------
    |
    | Default sidereal mode for sidereal calculations.
    | Set to null for tropical zodiac.
    |
    */
    'sidereal_mode' => env('SWISSEPH_SIDEREAL_MODE', null),
    
    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable logging of Swiss Ephemeris operations.
    |
    */
    'logging' => env('SWISSEPH_LOGGING', false),
];
