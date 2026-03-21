<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use SwissEph\FFI\SwissEphFFI;

$sweph = new SwissEphFFI();

// Set the directory for ephemeris files if necessary
// $sweph->swe_set_ephe_path('/path/to/ephe');

// Calculate Julian Day for 15 May 1990, 14:30 UT
$jd = $sweph->swe_julday(1990, 5, 15, 14.5, SwissEphFFI::SE_GREG_CAL);

echo "Julian Day: $jd\n\n";

// Get Lahiri ayanamsa
$sweph->swe_set_sid_mode(SwissEphFFI::SE_SIDM_LAHIRI, 0.0, 0.0);
$ayanamsa = $sweph->swe_get_ayanamsa_ut($jd);
echo "Ayanamsa (Lahiri): $ayanamsa°\n\n";

// Sidereal Sun Position
$xx = $sweph->getFFI()->new("double[6]");
$serr = $sweph->getFFI()->new("char[256]");
$sweph->swe_calc_ut($jd, SwissEphFFI::SE_SUN, SwissEphFFI::SEFLG_SIDEREAL | SwissEphFFI::SEFLG_SPEED, $xx, $serr);

echo "Sidereal Sun (Lahiri) Longitude: {$xx[0]}°\n";

// Sidereal Houses (Placidus)
$cusps = $sweph->getFFI()->new("double[13]");
$ascmc = $sweph->getFFI()->new("double[10]");

// To calculate sidereal houses, use swe_houses_ex2 or pass SEFLG_SIDEREAL
// $sweph->swe_houses_ex2 is also strictly bound
// In swephexp.h: int swe_houses_ex(double tjd_ut, int32 iflag, double geolat, double geolon, int hsys, double *cusps, double *ascmc);
$sweph->swe_houses_ex(
    $jd,
    SwissEphFFI::SEFLG_SIDEREAL,
    40.7128,
    -74.0060,
    ord(SwissEphFFI::SE_HOUSES_PLACIDUS),
    $cusps,
    $ascmc
);

echo "\nSidereal Ascendant: {$ascmc[0]}°\n";
for ($i = 1; $i <= 12; $i++) {
    echo "House $i Cusp: {$cusps[$i]}°\n";
}

// Reset to tropical
$sweph->swe_set_sid_mode(0, 0.0, 0.0);
