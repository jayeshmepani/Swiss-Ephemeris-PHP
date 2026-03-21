# Swiss Ephemeris PHP FFI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jayeshmepani/swiss-ephemeris-ffi.svg?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)
[![Total Downloads](https://img.shields.io/packagist/dt/jayeshmepani/swiss-ephemeris-ffi.svg?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)
[![PHP Version Require](https://img.shields.io/packagist/php-v/jayeshmepani/swiss-ephemeris-ffi?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)
[![License](https://img.shields.io/packagist/l/jayeshmepani/swiss-ephemeris-ffi.svg?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)

A strict, **100% precise, exact 1:1 FFI mapping** of the [Swiss Ephemeris](https://www.astro.com/swisseph/) C library for PHP 8.3+.

This wrapper gives you the exact API surface, structs, macro constants, and FFI pointers as defined in the original `swephexp.h`. Zero tolerance for omissions or high-level abstractions: every single calculation, planet ID, house system, sidereal mode, and eclipse check is strictly preserved for **maximum astronomical precision**.

## Features

- **100% C Library Coverage**: Every single exported function from `swephexp.h`.
- **Zero Loss / Round-offs**: Arguments and return values are explicitly `double` and `int32` via raw FFI arrays/pointers.
- **Native Performance**: Direct C calls via PHP's `ext-ffi`.
- **Pre-compiled Shared Object**: Ships with a pre-built `libswe.so` for Linux x64 for true zero-compilation use.
- **Laravel Ready**: Optional Service Provider and Facade included.

## Installation

```bash
composer require jayeshmepani/swiss-ephemeris-ffi
```

> **Note:** PHP FFI must be enabled. Ensure `ffi.enable=preload` (or `ffi.enable=true`) is configured in your `php.ini`.

For non-Linux x64 users, or if you wish to recompile the library manually from the original `swisseph` repository:

```bash
bash build/compile.sh
```

## Ephemeris Files

For precise planetary, lunar, and asteroidal calculations, download the official `.se1` ephemeris files:

1. Download files from [https://github.com/aloistr/swisseph/tree/master/ephe](https://github.com/aloistr/swisseph/tree/master/ephe)
2. Place them in your project (e.g., `/ephe`).
3. Point the library to them using `$sweph->swe_set_ephe_path('/path/to/ephe');`

*Many beginners forget this step, resulting in less accurate "Moshier" calculations being used as a fallback.*

## Usage

Because this is a **1:1 strict binding**, you must pass arguments via FFI pointers (`CData`) just as you would in C.

### Tropical Zodiac (Sun Position)

```php
<?php
use SwissEph\FFI\SwissEphFFI;

$sweph = new SwissEphFFI();

// Set up Julian Day
$jd = $sweph->swe_julday(2000, 1, 1, 12.0, SwissEphFFI::SE_GREG_CAL);

// FFI Pointers for results
$xx = $sweph->getFFI()->new("double[6]");
$serr = $sweph->getFFI()->new("char[256]");

// Planet calculation
$result = $sweph->swe_calc_ut(
    $jd,
    SwissEphFFI::SE_SUN,
    SwissEphFFI::SEFLG_SPEED,
    $xx,
    $serr
);

if ($result >= 0) { // OK
    echo "Sun Longitude: " . $xx[0] . "°\n";
    echo "Sun Speed: " . $xx[3] . "°/day\n";
} else {
    echo "Error: " . \FFI::string($serr);
}
```

### Sidereal Zodiac (Vedic Astrology / Lahiri)

```php
<?php
use SwissEph\FFI\SwissEphFFI;

$sweph = new SwissEphFFI();
$jd = $sweph->swe_julday(1990, 5, 15, 14.5, SwissEphFFI::SE_GREG_CAL);

// Set Ayanamsa
$sweph->swe_set_sid_mode(SwissEphFFI::SE_SIDM_LAHIRI, 0.0, 0.0);

$xx = $sweph->getFFI()->new("double[6]");
$serr = $sweph->getFFI()->new("char[256]");

// Note the SEFLG_SIDEREAL flag
$sweph->swe_calc_ut(
    $jd,
    SwissEphFFI::SE_MOON,
    SwissEphFFI::SEFLG_SIDEREAL | SwissEphFFI::SEFLG_SPEED,
    $xx,
    $serr
);

echo "Sidereal Moon (Lahiri): " . $xx[0] . "°\n";

// Sidereal Houses (e.g. Placidus)
$cusps = $sweph->getFFI()->new("double[13]");
$ascmc = $sweph->getFFI()->new("double[10]");

$sweph->swe_houses_ex(
    $jd,
    SwissEphFFI::SEFLG_SIDEREAL,
    40.7128,
    -74.0060,
    ord(SwissEphFFI::SE_HOUSES_PLACIDUS),
    $cusps,
    $ascmc
);

echo "Sidereal Ascendant: " . $ascmc[0] . "°\n";
```

More comprehensive examples can be found in the `examples/` directory.

## Testing

Ensure `composer install` is run, then execute the test suite:

```bash
composer test
```

## Credits

- [Jayesh Mepani](https://github.com/jayeshmepani)
- [Dieter Koch and Alois Treindl](https://www.astro.com/swisseph/) (Original Swiss Ephemeris C Library Authors)

## License

This library is licensed under the GPL-2.0-or-later, mirroring the standard licensing options of the underlying Swiss Ephemeris project. See the [LICENSE](LICENSE) file for details.
