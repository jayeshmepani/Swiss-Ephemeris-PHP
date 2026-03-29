<?php

declare(strict_types=1);

/**
 * Precision Demo: Moshier vs Swiss Ephemeris Files.
 *
 * This example shows the ACTUAL raw precision difference between:
 * - Moshier algorithm (built-in, no external files) - ~1 arcsecond precision
 * - Swiss Ephemeris files (external .se1 files) - milliarcsecond precision
 *
 * Run with: php examples/precision_demo.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use SwissEph\FFI\SwissEphFFI;

echo "===========================================\n";
echo "  Precision Demo: Moshier vs Swiss Eph\n";
echo "===========================================\n\n";

$sweph = new SwissEphFFI;

// Test date: May 15, 1990, 14:30 UT (Michael Jackson's birth)
$jd = $sweph->swe_julday(1990, 5, 15, 14.5, SwissEphFFI::SE_GREG_CAL);
echo "Julian Day: $jd\n";
echo "Date: May 15, 1990, 14:30 UT\n\n";

// Allocate C arrays
$xx = $sweph->getFFI()->new('double[6]');
$serr = $sweph->getFFI()->new('char[256]');

// Test planets - Moon shows biggest difference
$planets = [
    'Moon' => SwissEphFFI::SE_MOON,
    'Sun' => SwissEphFFI::SE_SUN,
    'Mercury' => SwissEphFFI::SE_MERCURY,
    'Venus' => SwissEphFFI::SE_VENUS,
    'Mars' => SwissEphFFI::SE_MARS,
    'Jupiter' => SwissEphFFI::SE_JUPITER,
    'Saturn' => SwissEphFFI::SE_SATURN,
    'Uranus' => SwissEphFFI::SE_URANUS,
    'Neptune' => SwissEphFFI::SE_NEPTUNE,
    'Pluto' => SwissEphFFI::SE_PLUTO,
];

echo "=== WITHOUT Ephemeris Files (Moshier - built-in) ===\n";
echo "---------------------------------------------------\n";

$moshierPositions = [];
foreach ($planets as $name => $id) {
    $sweph->swe_calc_ut($jd, $id, SwissEphFFI::SEFLG_SPEED, $xx, $serr);
    $moshierPositions[$name] = $xx[0];
    echo "$name: $xx[0]\n";
}

echo "\n";
echo "=== WITH Swiss Ephemeris Files ===\n";
echo "-----------------------------------\n";

// Set path to ephe files
$ephePath = __DIR__ . '/../swisseph/ephe';
$sweph->swe_set_ephe_path($ephePath);

$swissPositions = [];
foreach ($planets as $name => $id) {
    $sweph->swe_calc_ut($jd, $id, SwissEphFFI::SEFLG_SPEED, $xx, $serr);
    $swissPositions[$name] = $xx[0];
    echo "$name: $xx[0]\n";
}

echo "\n";
echo "=== DIFFERENCE (Swiss Eph - Moshier) ===\n";
echo "==========================================\n";

foreach ($planets as $name => $id) {
    $diff = $swissPositions[$name] - $moshierPositions[$name];
    $arcsec = $diff * 3600;
    echo "$name: $diff (in degrees)\n";
    echo "$name: $arcsec (in arcseconds)\n\n";
}

echo "Done!\n";
