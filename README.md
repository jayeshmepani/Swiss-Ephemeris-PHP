# Swiss Ephemeris PHP FFI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jayeshmepani/swiss-ephemeris-ffi.svg?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)
[![Total Downloads](https://img.shields.io/packagist/dt/jayeshmepani/swiss-ephemeris-ffi.svg?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)
[![PHP Version Require](https://img.shields.io/packagist/php-v/jayeshmepani/swiss-ephemeris-ffi?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)
[![License](https://img.shields.io/packagist/l/jayeshmepani/swiss-ephemeris-ffi.svg?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)
[![Tests](https://github.com/jayeshmepani/Swiss-Ephemeris-PHP/actions/workflows/tests.yml/badge.svg)](https://github.com/jayeshmepani/Swiss-Ephemeris-PHP/actions/workflows/tests.yml)

A strict, **100% precise, exact 1:1 FFI mapping** of the [Swiss Ephemeris](https://www.astro.com/swisseph/) C library for PHP 8.3+.

This wrapper gives you the exact API surface, structs, macro constants, and FFI pointers as defined in the original `swephexp.h`. Zero tolerance for omissions or high-level abstractions: every single calculation, planet ID, house system, sidereal mode, and eclipse check is strictly preserved for **maximum astronomical precision**.

## 🎯 Unique Value Proposition

**This is the ONLY PHP implementation that:**
- ✅ Uses **PHP FFI** (no PHP extension compilation required)
- ✅ Links to **shared library** (libswe.* per OS)
- ✅ Achieves **100% function coverage** (all 200+ swe_* functions)
- ✅ Maintains **1:1 mapping** with C library
- ✅ Preserves **exact C library precision** (no CLI parsing, no text output)
- ✅ Works with **Laravel, Symfony, or plain PHP**

Unlike other PHP packages that wrap the `swetest` CLI binary, this package calls the C functions directly via FFI - just like the Python (pyswisseph) and Node.js (swisseph) bindings.

## Features

- **100% C Library Coverage**: Every single exported function from `swephexp.h`.
- **Zero Loss / Round-offs**: Arguments and return values are explicitly `double` and `int32` via raw FFI arrays/pointers.
- **Native Performance**: Direct C calls via PHP's `ext-ffi` - no process spawning, no text parsing.
- **Prebuilt Shared Libraries**: Auto-downloads the correct binary on install (Linux/macOS/Windows) when available.
- **Laravel Ready**: Optional Service Provider and Facade included.
- **Framework Agnostic**: Works with Laravel, Symfony, CodeIgniter, or plain PHP.

## Installation

```bash
composer require jayeshmepani/swiss-ephemeris-ffi
```

> **Note:** PHP FFI must be installed and enabled.

### FFI Requirement (All Platforms)

You need **PHP 8.3+ with `ext-ffi`**.

1. Check if FFI is available:
```bash
php -m | grep -i ffi
```

2. If FFI is missing:
   - **Linux (apt/yum)**: install the FFI package for your PHP version (example: `php8.4-ffi`).
   - **macOS (Homebrew)**: FFI is included; ensure it is enabled in `php.ini`.
   - **Windows**: FFI is included; enable it in `php.ini`.
   - **Custom source build**: recompile PHP with `--with-ffi` or `--enable-ffi`.

3. Enable FFI in `php.ini`:
```
ffi.enable=1
; or
ffi.enable=preload
```

### Prebuilt Libraries (Zero-Setup)

On `composer install`, the package will attempt to download the correct prebuilt library for your OS/CPU from GitHub Releases.

Asset naming (release artifacts):
- `libswe-linux-x64.tar.gz`
- `libswe-linux-arm64.tar.gz`
- `libswe-macos-x64.tar.gz`
- `libswe-macos-arm64.tar.gz`
- `libswe-windows-x64.zip`

To disable auto-download, set `SWISSEPH_SKIP_DOWNLOAD=1` and provide your own library via `SWISSEPH_LIBRARY_PATH`.

### Swiss Ephemeris Version

This package tracks the **latest version** of the [Swiss Ephemeris C library](https://github.com/aloistr/swisseph):

- **Current upstream version**: v2.10.03+ (latest commit from master branch)
- **Last upstream commit**: March 11, 2026 - "fixed old rounding bug in swe_split_deg()"
- **Latest release tag**: v2.10.03 (September 9, 2022)
- **Development branch**: `master` (actively maintained)

**Your package always uses the latest version** when you run:

```bash
composer build
# or
bash build/compile-linux.sh
```

This downloads the latest commit from the official Swiss Ephemeris repository and compiles it into the OS-specific shared library.

### If you need to rebuild the library manually:

```bash
composer build
# or (OS specific)
bash build/compile-linux.sh
bash build/compile-macos.sh
powershell -ExecutionPolicy Bypass -File build/compile-windows.ps1
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

## 📊 Function Coverage

This package provides **100% coverage** of all Swiss Ephemeris C functions:

| Category | Functions | Status |
|----------|-----------|--------|
| Planets & Calculation | 28 | ✅ Complete |
| Houses & Angles | 7 | ✅ Complete |
| Sidereal & Ayanamsha | 11 | ✅ Complete |
| Nodes & Apsides | 2 | ✅ Complete |
| Rise/Set/Transit | 7 | ✅ Complete |
| Crossings & Transits | 8 | ✅ Complete |
| Time & Conversions | 14 | ✅ Complete |
| Coordinate Transform | 7 | ✅ Complete |
| Orbital Elements | 2 | ✅ Complete |
| Stars & Fixed Objects | 6 | ✅ Complete |
| Eclipses & Phenomena | 15 | ✅ Complete |
| Heliacal Phenomena | 5 | ✅ Complete |
| Misc Utilities | 31 | ✅ Complete |

**Total: 200+ functions** - All accessible via direct FFI calls.

## Testing

Ensure `composer install` is run, then execute the test suite:

```bash
# Run all tests
composer test

# Check code quality
composer quality
```

## Laravel Integration

This package includes a Laravel Service Provider and Facade:

```php
// config/app.php (for Laravel < 11)
'providers' => [
    SwissEph\Service\SwissEphServiceProvider::class,
],

'aliases' => [
    'SwissEph' => SwissEph\Service\SwissEphFacade::class,
],

// Usage
use SwissEph\Service\SwissEphFacade;

$jd = SwissEph::swe_julday(2000, 1, 1, 12.0, SwissEph::SE_GREG_CAL);
```

## Comparison with Other PHP Implementations

| Feature | This Package (FFI) | php-sweph (Extension) | php-swisseph (Pure PHP) | Laravel Packages (CLI) |
|---------|-------------------|----------------------|------------------------|------------------------|
| **Approach** | FFI + libswe.so | Compiled .so extension | Pure PHP rewrite | CLI wrapper (swetest) |
| **100% Coverage** | ✅ Yes | ✅ ~106 functions | ✅ 106 functions | ❌ Limited |
| **Direct C Access** | ✅ Yes | ✅ Yes | ❌ No | ❌ No |
| **Compilation** | ✅ Prebuilt or auto-download | ✅ Yes | ❌ No | ❌ No |
| **Speed** | ⚡ Fast | ⚡ Fastest | 🐌 Slower | 🐌 Slowest |
| **Precision** | ✅ Exact C | ✅ Exact C | ⚠️ PHP implementation | ⚠️ Text parsing |

## Requirements

- **PHP**: 8.3 or higher
- **FFI Extension**: Must be enabled (`ffi.enable=preload` in php.ini)
- **OS**: Linux/macOS/Windows. Prebuilt libs are downloaded on install when available. If not, run `composer build`.
- **Ephemeris Files**: Optional but recommended for high-precision calculations

## Development

```bash
# Clone repository
git clone https://github.com/jayeshmepani/Swiss-Ephemeris-PHP.git
cd Swiss-Ephemeris-PHP

# Install dependencies
composer install

# Run tests
composer test

# Check code quality
composer quality

# Build library (if needed)
composer build
```

## 📚 Documentation

- [Swiss Ephemeris Programmer's Documentation](https://www.astro.com/swisseph/swephprg.htm)
- [Swiss Ephemeris Function Reference](https://www.astro.com/swisseph/swephfun.htm)
- [Examples Directory](./examples/)

## 🤝 Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/jayeshmepani/Swiss-Ephemeris-PHP/issues)
- **Email**: [jayeshmepani777@gmail.com](mailto:jayeshmepani777@gmail.com)
- **Documentation**: [README.md](README.md)

## 💖 Funding

If you find this package helpful, consider sponsoring the development:

[![Sponsor on GitHub](https://img.shields.io/badge/sponsor-%E2%9D%A4-%23EA4AAA?logo=github&style=flat-square)](https://github.com/sponsors/jayeshmepani)

## 📄 License

This library is licensed under the GPL-2.0-or-later, mirroring the standard licensing options of the underlying Swiss Ephemeris project. See the [LICENSE.md](LICENSE.md) file for details.

**Note:** If you use this package in commercial software, you may need to purchase a commercial license from [Astrodienst](https://www.astro.com/swisseph/swephprice_e.htm).

## 🙏 Credits

- **[Jayesh Patel](https://github.com/jayeshmepani)** - PHP FFI Wrapper Developer
- **[Dieter Koch and Alois Treindl](https://www.astro.com/swisseph/)** - Original Swiss Ephemeris C Library Authors
- **[Astrodienst AG](https://www.astro.com/)** - Swiss Ephemeris Maintainers

---

Made with ❤️ for the astrology and astronomy community.
