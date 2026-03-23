<?php

declare(strict_types=1);

/**
 * Sidereal Zodiac Example (Lahiri Ayanamsa).
 *
 * Demonstrates sidereal zodiac calculations using Lahiri ayanamsa,
 * the most common system in Vedic astrology (Jyotish).
 *
 * Key differences from tropical zodiac:
 * - Accounts for axial precession (26,000-year wobble of Earth's axis)
 * - Aligns with fixed star positions (sidereal reference frame)
 * - Uses ayanamsa (precession correction) value
 *
 * @see https://www.astro.com/swisseph/swephprg.htm Swiss Ephemeris Documentation
 */

require_once __DIR__ . '/../vendor/autoload.php';

use SwissEph\FFI\SwissEphFFI;

$sweph = new SwissEphFFI;

// Calculate Julian Day for May 15, 1990, 14:30 UT
$julianDay = $sweph->swe_julday(1990, 5, 15, 14.5, SwissEphFFI::SE_GREG_CAL);

echo "Julian Day: $julianDay\n\n";

// Set sidereal mode to Lahiri (Indian standard ayanamsa)
// Parameters: mode, t0 (start time), ayan_t0 (ayanamsa at t0)
$sweph->swe_set_sid_mode(SwissEphFFI::SE_SIDM_LAHIRI, 0.0, 0.0);

// Get Lahiri ayanamsa value for the date
// Year 2000: ~23.85°, increases by ~50 arcseconds per year
$ayanamsa = $sweph->swe_get_ayanamsa_ut($julianDay);
echo "Ayanamsa (Lahiri): {$ayanamsa}°\n\n";

// Calculate sidereal Sun position
$xx = $sweph->getFFI()->new('double[6]');
$serr = $sweph->getFFI()->new('char[256]');
$sweph->swe_calc_ut($julianDay, SwissEphFFI::SE_SUN, SwissEphFFI::SEFLG_SIDEREAL | SwissEphFFI::SEFLG_SPEED, $xx, $serr);

echo "Sidereal Sun (Lahiri) Longitude: {$xx[0]}°\n";

// Calculate sidereal house cusps (Placidus system)
// Note: SEFLG_SIDEREAL flag must be passed to swe_houses_ex()
$cusps = $sweph->getFFI()->new('double[13]');
$ascmc = $sweph->getFFI()->new('double[10]');

$sweph->swe_houses_ex(
    $julianDay,
    SwissEphFFI::SEFLG_SIDEREAL,  // Sidereal flag
    40.7128,  // Latitude (New York)
    -74.0060, // Longitude
    ord(SwissEphFFI::SE_HOUSES_PLACIDUS),  // House system
    $cusps,
    $ascmc
);

echo "\nSidereal Ascendant: {$ascmc[0]}°\n";

for ($i = 1; $i <= 12; $i++) {
    echo "House $i Cusp: {$cusps[$i]}°\n";
}

// Reset to tropical zodiac (default)
$sweph->swe_set_sid_mode(0, 0.0, 0.0);
