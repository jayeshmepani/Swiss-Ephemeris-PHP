# Swiss Ephemeris PHP FFI

[![PHP Version](https://img.shields.io/packagist/php-v/jayeshmepani/swiss-ephemeris-ffi)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)
[![License](https://img.shields.io/github/license/jayeshmepani/Swiss-Ephemeris-PHP)](LICENSE)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/jayeshmepani/swiss-ephemeris-ffi.svg?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)
[![Total Downloads](https://img.shields.io/packagist/dt/jayeshmepani/swiss-ephemeris-ffi.svg?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)

PHP 8.3+ FFI wrapper for the Swiss Ephemeris C library.

This package is designed to expose the Swiss Ephemeris C API to PHP through FFI, without shelling out to the `swetest` command-line tool.

**Zero abstraction. Native-level FFI. Verified output parity with Swiss Ephemeris C engine.**

> Swiss Ephemeris PHP FFI provides a zero-abstraction, 1:1 mapping of the native Swiss Ephemeris C library. All 106 public API functions are exposed with complete constant and signature parity.
>
> The wrapper performs no additional calculations, transformations, or rounding, ensuring direct memory-level interaction with the C engine.
>
> Outputs are verified against the official `swetest` CLI using automated PHPUnit tests, demonstrating bit-level parity in verified test scenarios for planetary positions, house systems, eclipses, and edge-date calculations.

## Latest Upstream Status

Checked against upstream on **April 25, 2026**.

- **Latest upstream release tag**: `v2.10.3final` released on **April 14, 2026**.
- **Current upstream `master` checked**: commit `2f18c14` from **April 18, 2026** (`fixed bug in semo4200.se1`).
- **Swiss Ephemeris 3.0**: announced by upstream as the next major source-code release, but no public `v3.0` tag was available at the time of this check.
- **DE441 data update**: upstream states that, as of **April 14, 2026**, all `.se1` data files for planets and asteroids were rebuilt with JPL Ephemeris DE441.
- **Compatibility note**: upstream states that the rebuilt `.se1` files remain compatible with older Swiss Ephemeris versions at least back to release 1.67.
- **Asteroid data**: upstream documents more than **760,000 numbered asteroids** and more than **25,000 named asteroids**. Commit history also records asteroid list updates through numbered asteroid `793066`.
- **Internal Swiss Ephemeris version string**: the upstream C header still defines `SE_VERSION` as `2.10.03`.

See [`VERSION.md`](VERSION.md) and [`UPSTREAM_SYNC.md`](UPSTREAM_SYNC.md) for detailed version tracking.

## Requirements

- PHP `^8.3`
- PHP FFI extension (`ext-ffi`)
- FFI enabled in PHP configuration. Depending on your environment, this usually means setting `ffi.enable=true`, or configuring FFI preload mode correctly.

## Installation

```bash
composer require jayeshmepani/swiss-ephemeris-ffi
```

## Quick Start

```php
use SwissEph\FFI\SwissEphFFI;

$sweph = new SwissEphFFI();

$jd = $sweph->swe_julday(2000, 1, 1, 12.0, SwissEphFFI::SE_GREG_CAL);

$xx = $sweph->getFFI()->new("double[6]");
$serr = $sweph->getFFI()->new("char[256]");

$result = $sweph->swe_calc_ut(
    $jd,
    SwissEphFFI::SE_SUN,
    SwissEphFFI::SEFLG_SPEED,
    $xx,
    $serr
);

if ($result >= 0) {
    echo "Sun Longitude: " . $xx[0] . "°\n";
} else {
    echo "Swiss Ephemeris error: " . $sweph->getFFI()->string($serr) . "\n";
}
```

## Documentation

**[Read the full documentation →](https://jayeshmepani.github.io/Swiss-Ephemeris-PHP/)**

The documentation covers:

- Installation on Linux, macOS, and Windows
- FFI configuration
- Ephemeris file setup
- Swiss Ephemeris API usage
- Examples for tropical, sidereal, and house calculations
- Laravel integration
- Troubleshooting

## Verification Sources

- Upstream repository: <https://github.com/aloistr/swisseph>
- Upstream releases: <https://github.com/aloistr/swisseph/releases>
- Upstream compare: <https://github.com/aloistr/swisseph/compare/v2.10.3final...master>
- Packagist package: <https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi>
- PHP FFI configuration: <https://www.php.net/manual/en/ffi.configuration.php>
- Swiss Ephemeris official information: <https://www.astro.com/swisseph/swephinfo_e.htm>

## License

This package metadata declares **AGPL-3.0-or-later**.

The upstream Swiss Ephemeris C library and ephemeris data are distributed under Astrodienst's dual licensing model: **AGPL** or **Swiss Ephemeris Professional License**. If you use Swiss Ephemeris in commercial or closed-source software, or in a public SaaS/web service, review Astrodienst's license terms before use.
