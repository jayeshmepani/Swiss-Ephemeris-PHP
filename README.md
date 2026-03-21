# Swiss Ephemeris PHP FFI

Complete PHP FFI wrapper for the Swiss Ephemeris C library with 100% function coverage.

## Features

- **100% C Library Coverage**: All functions from swephexp.h
- **Native Performance**: Direct C calls via FFI
- **Laravel Ready**: Service provider and facade included
- **PHP 8.3+**: Modern type system and FFI improvements

## Installation

```bash
composer require swisseph/php-ffi
bash build/compile.sh
```

Enable FFI in php.ini:
```ini
ffi.enable=preload
```

## Usage

```php
<?php
use SwissEph\FFI\SwissEphFFI;

$sweph = new SwissEphFFI();

// Planet position
$jd = $sweph->swe_julday(2000, 1, 1, 12.0);
$sun = $sweph->swe_calc_ut($jd, SwissEphFFI::SE_SUN);
echo "Sun: {$sun['longitude']}°\n";

// Houses
$houses = $sweph->swe_houses($jd, 40.7128, -74.0060, 'P');
echo "Ascendant: {$houses['ascendant']}°\n";
```

## Available Functions

### Initialization
- `swe_set_epath()`, `swe_get_epath()`
- `swe_set_jpl_file()`, `swe_close()`, `swe_version()`
- `swe_set_debug_level()`, `swe_get_debug_level()`

### Planet Calculations
- `swe_calc_ut()`, `swe_calc()`
- `swe_fixstar_ut()`, `swe_fixstar()`

### Houses
- `swe_houses()`, `swe_houses_armc()`, `swe_house_pos()`

### Time
- `swe_julday()`, `swe_revjul()`, `swe_deltat()`, `swe_deltat_ex()`

### Sidereal
- `swe_set_sid_mode()`, `swe_get_ayanamsa()`, `swe_get_ayanamsa_ut()`

### Eclipses
- `swe_sol_eclipse_when_loc()`, `swe_lun_eclipse_when_loc()`
- `swe_sol_eclipse_how()`

### Phenomena
- `swe_pheno()`, `swe_refrac()`

### Rise/Set
- `swe_rise_trans()`

### Azimuth/Altitude
- `swe_azalt()`

### Nodes/Apsides
- `swe_nod_aps()`

### Utilities
- `swe_split_deg()`, `swe_cotrans()`, `swe_precess()`
- `swe_get_constellation()`, `swe_get_planet_name()`
- `swe_get_ecliptic_obliquity()`, `swe_nutation()`, `swe_time_equ()`

## Constants

```php
// Planets
SwissEphFFI::SE_SUN, SwissEphFFI::SE_MOON, SwissEphFFI::SE_MERCURY, ...

// Flags
SwissEphFFI::SEFLG_SWIEPH, SwissEphFFI::SEFLG_SPEED, ...

// House Systems
SwissEphFFI::SE_HOUSES_PLACIDUS, SwissEphFFI::SE_HOUSES_KOCH, ...
```

## Testing

```bash
composer test
```

## License

GPL-2.0-or-later
