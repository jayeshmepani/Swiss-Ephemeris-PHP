# Swiss Ephemeris PHP FFI

[![PHP Version](https://img.shields.io/packagist/php-v/jayeshmepani/swiss-ephemeris-ffi)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)
[![License](https://img.shields.io/github/license/jayeshmepani/Swiss-Ephemeris-PHP)](LICENSE)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/jayeshmepani/swiss-ephemeris-ffi.svg?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)
[![Total Downloads](https://img.shields.io/packagist/dt/jayeshmepani/swiss-ephemeris-ffi.svg?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)


100% precise 1:1 FFI mapping of the Swiss Ephemeris C library for PHP 8.3+. Complete wrapper for all 106 functions.

## Requirements

- PHP 8.3+ with FFI extension
- `ffi.enable=1` in php.ini

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

$result = $sweph->swe_calc_ut($jd, SwissEphFFI::SE_SUN, SwissEphFFI::SEFLG_SPEED, $xx, $serr);

if ($result >= 0) {
    echo "Sun Longitude: " . $xx[0] . "°\n";
}
```

## Documentation

**[Read the full documentation →](https://jayeshmepani.github.io/Swiss-Ephemeris-PHP/)**

The documentation covers:
- Installation (Linux, macOS, Windows)
- FFI configuration
- Ephemeris files setup
- All 106 API functions
- Examples (tropical, sidereal, houses)
- Laravel integration
- Troubleshooting

## License

AGPL-3.0. See [LICENSE](LICENSE) for details.
