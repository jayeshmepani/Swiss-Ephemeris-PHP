<?php

declare(strict_types=1);

/**
 * Birth Chart Calculation Example.
 *
 * Demonstrates complete birth chart calculation including:
 * - Planetary positions (tropical and sidereal)
 * - House cusps (Placidus system)
 * - Ascendant and Midheaven
 * - Ayanamsa calculation
 *
 * @see https://www.astro.com/swisseph/swephprg.htm Swiss Ephemeris Documentation
 */

require_once __DIR__ . '/../vendor/autoload.php';

use SwissEph\FFI\SwissEphFFI;

// Initialize Swiss Ephemeris
$sweph = new SwissEphFFI;

// OPTIONAL: Set path to ephemeris files for higher precision
// Download files from: https://github.com/jayeshmepani/Swiss-Ephemeris-PHP/releases/tag/ephe-files
// Or from upstream: https://github.com/aloistr/swisseph/tree/master/ephe
// $sweph->swe_set_ephe_path(__DIR__ . '/ephe');

// Get and display library version
$versionStr = $sweph->getFFI()->new('char[256]');
$sweph->swe_version($versionStr);
echo 'Swiss Ephemeris Version: ' . FFI::string($versionStr) . "\n\n";

// Check which ephemeris is being used
$tfstart = $sweph->getFFI()->new('double');
$tfend = $sweph->getFFI()->new('double');
$denum = $sweph->getFFI()->new('int32');
$fileData = $sweph->swe_get_current_file_data(SwissEphFFI::SE_SUN, $tfstart, $tfend, $denum);
echo 'Ephemeris file in use: ' . ($fileData ? FFI::string($fileData) : 'Moshier (built-in)') . "\n\n";

// Birth data: May 15, 1990, 14:30 UT, New York
$birthData = [
    'year' => 1990,
    'month' => 5,
    'day' => 15,
    'hour' => 14.5,
    'latitude' => 40.7128,
    'longitude' => -74.0060,
    'timezone' => -5.0,
];

echo "Birth Chart Calculation\n";
echo "=======================\n";
echo "Date: {$birthData['year']}-{$birthData['month']}-{$birthData['day']}\n";
echo "Time: {$birthData['hour']} (UT)\n";
echo "Location: {$birthData['latitude']}, {$birthData['longitude']}\n\n";

// Convert birth date to Julian Day (continuous time scale used in astronomy)
$julianDay = $sweph->swe_julday(
    $birthData['year'],
    $birthData['month'],
    $birthData['day'],
    $birthData['hour'],
    SwissEphFFI::SE_GREG_CAL
);

echo "Julian Day: $julianDay\n\n";

// Planet IDs from Swiss Ephemeris
$planets = [
    'Sun' => SwissEphFFI::SE_SUN,
    'Moon' => SwissEphFFI::SE_MOON,
    'Mercury' => SwissEphFFI::SE_MERCURY,
    'Venus' => SwissEphFFI::SE_VENUS,
    'Mars' => SwissEphFFI::SE_MARS,
    'Jupiter' => SwissEphFFI::SE_JUPITER,
    'Saturn' => SwissEphFFI::SE_SATURN,
    'Uranus' => SwissEphFFI::SE_URANUS,
    'Neptune' => SwissEphFFI::SE_NEPTUNE,
    'Pluto' => SwissEphFFI::SE_PLUTO,
    'Mean Node' => SwissEphFFI::SE_MEAN_NODE,
    'True Node' => SwissEphFFI::SE_TRUE_NODE,
];

echo "Planetary Positions (Tropical Zodiac)\n";
echo "--------------------------------------\n";

// Allocate C arrays for results
$xx = $sweph->getFFI()->new('double[6]');  // 6 values: lon, lat, dist, speed_lon, speed_lat, speed_dist
$serr = $sweph->getFFI()->new('char[256]'); // Error message buffer
$signs = ['Ari', 'Tau', 'Gem', 'Can', 'Leo', 'Vir', 'Lib', 'Sco', 'Sag', 'Cap', 'Aqu', 'Pis'];

foreach ($planets as $name => $id) {
    // Calculate planet position with speed
    $result = $sweph->swe_calc_ut($julianDay, $id, SwissEphFFI::SEFLG_SPEED, $xx, $serr);

    if ($result !== SwissEphFFI::ERR) {
        // Convert longitude to sign-degree format
        $sign = (int) floor($xx[0] / 30);
        $degree = $xx[0] - ($sign * 30);

        printf("%-12s: %3s %8.2f° (Speed: %+.4f°/day)\n",
            $name,
            $signs[$sign],
            $degree,
            $xx[3]
        );
    }
}

echo "\n";

// Calculate house cusps using Placidus system
echo "House Cusps (Placidus)\n";
echo "-----------------------\n";

$cusps = $sweph->getFFI()->new('double[13]');  // 12 cusps + 1 unused
$ascmc = $sweph->getFFI()->new('double[10]');  // Ascendant, MC, and other sensitive points

$result = $sweph->swe_houses(
    $julianDay,
    $birthData['latitude'],
    $birthData['longitude'],
    ord(SwissEphFFI::SE_HOUSES_PLACIDUS),  // 'P' = Placidus
    $cusps,
    $ascmc
);

if ($result !== SwissEphFFI::ERR) {
    for ($i = 1; $i <= 12; $i++) {
        $sign = (int) floor($cusps[$i] / 30);
        $degree = $cusps[$i] - ($sign * 30);

        printf("House %2d: %3s %8.2f°\n", $i, $signs[$sign], $degree);
    }

    echo "\n";
    // Ascendant (1st house cusp)
    printf("Ascendant: %3s %8.2f°\n",
        $signs[(int) floor($ascmc[0] / 30)],
        $ascmc[0] - floor($ascmc[0] / 30) * 30
    );
    // Midheaven (10th house cusp)
    printf("Midheaven (MC): %3s %8.2f°\n",
        $signs[(int) floor($ascmc[1] / 30)],
        $ascmc[1] - floor($ascmc[1] / 30) * 30
    );
}

echo "\n";

// Get Lahiri ayanamsa (precession correction for sidereal calculations)
$ayanamsa = $sweph->swe_get_ayanamsa_ut($julianDay);
echo "Ayanamsa (Lahiri): {$ayanamsa}°\n";

// Calculate sidereal positions (Vedic astrology)
echo "\nSidereal Positions (with Ayanamsa)\n";
echo "-----------------------------------\n";

// Set sidereal mode to Lahiri (most common in Vedic astrology)
$sweph->swe_set_sid_mode(SwissEphFFI::SE_SIDM_LAHIRI, 0.0, 0.0);

foreach (array_slice($planets, 0, 7) as $name => $id) {
    $result = $sweph->swe_calc_ut($julianDay, $id, SwissEphFFI::SEFLG_SIDEREAL, $xx, $serr);

    if ($result !== SwissEphFFI::ERR) {
        $sign = (int) floor($xx[0] / 30);
        $degree = $xx[0] - ($sign * 30);

        printf("%-12s: %3s %8.2f°\n", $name, $signs[$sign], $degree);
    }
}

// Reset to tropical zodiac
$sweph->swe_set_sid_mode(0, 0.0, 0.0);

echo "\nDone!\n";
