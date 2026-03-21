<?php

declare(strict_types=1);

/**
 * Birth Chart Calculation Example
 * 
 * This example demonstrates how to calculate a complete birth chart
 * including planetary positions and house cusps.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use SwissEph\FFI\SwissEphFFI;

// Initialize Swiss Ephemeris
$sweph = new SwissEphFFI();

$versionStr = $sweph->getFFI()->new("char[256]");
$sweph->swe_version($versionStr);
echo "Swiss Ephemeris Version: " . \FFI::string($versionStr) . "\n\n";

// Birth data
$birthData = [
    'year' => 1990,
    'month' => 5,
    'day' => 15,
    'hour' => 14.5, // 14:30
    'latitude' => 40.7128, // New York
    'longitude' => -74.0060,
    'timezone' => -5.0, // EST
];

echo "Birth Chart Calculation\n";
echo "=======================\n";
echo "Date: {$birthData['year']}-{$birthData['month']}-{$birthData['day']}\n";
echo "Time: {$birthData['hour']} (UT)\n";
echo "Location: {$birthData['latitude']}, {$birthData['longitude']}\n\n";

// Convert to Julian Day
$julianDay = $sweph->swe_julday(
    $birthData['year'],
    $birthData['month'],
    $birthData['day'],
    $birthData['hour'],
    SwissEphFFI::SE_GREG_CAL
);

echo "Julian Day: $julianDay\n\n";

// Calculate planetary positions
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

$xx = $sweph->getFFI()->new("double[6]");
$serr = $sweph->getFFI()->new("char[256]");

foreach ($planets as $name => $id) {
    $result = $sweph->swe_calc_ut($julianDay, $id, SwissEphFFI::SEFLG_SPEED, $xx, $serr);
    
    if ($result !== SwissEphFFI::ERR) {
        $longitude = $xx[0];
        $longitude_speed = $xx[3];

        // Convert to sign-degree format
        $sign = floor($longitude / 30);
        $degree = $longitude - ($sign * 30);
        
        $signs = ['Ari', 'Tau', 'Gem', 'Can', 'Leo', 'Vir', 
                  'Lib', 'Sco', 'Sag', 'Cap', 'Aqu', 'Pis'];
        
        printf("%-12s: %3s %8.2f° (Speed: %+.4f°/day)\n",
            $name,
            $signs[(int)$sign],
            $degree,
            $longitude_speed
        );
    }
}

echo "\n";

// Calculate house cusps
echo "House Cusps (Placidus)\n";
echo "-----------------------\n";

$cusps = $sweph->getFFI()->new("double[13]");
$ascmc = $sweph->getFFI()->new("double[10]");

$result = $sweph->swe_houses(
    $julianDay,
    $birthData['latitude'],
    $birthData['longitude'],
    ord(SwissEphFFI::SE_HOUSES_PLACIDUS),
    $cusps,
    $ascmc
);

if ($result !== SwissEphFFI::ERR) {
    for ($i = 1; $i <= 12; $i++) {
        $longitude = $cusps[$i];
        $sign = floor($longitude / 30);
        $degree = $longitude - ($sign * 30);
        
        $signs = ['Ari', 'Tau', 'Gem', 'Can', 'Leo', 'Vir', 
                  'Lib', 'Sco', 'Sag', 'Cap', 'Aqu', 'Pis'];
        
        printf("House %2d: %3s %8.2f°\n", $i, $signs[(int)$sign], $degree);
    }
    
    echo "\n";
    $ascendant = $ascmc[0];
    $mc = $ascmc[1];

    printf("Ascendant: %3s %8.2f°\n", 
        $signs[(int)floor($ascendant / 30)],
        $ascendant - floor($ascendant / 30) * 30
    );
    printf("Midheaven (MC): %3s %8.2f°\n", 
        $signs[(int)floor($mc / 30)],
        $mc - floor($mc / 30) * 30
    );
}

echo "\n";

// Get ayanamsa
$ayanamsa = $sweph->swe_get_ayanamsa_ut($julianDay);
echo "Ayanamsa (Lahiri): $ayanamsa°\n";

// Calculate sidereal positions
echo "\nSidereal Positions (with Ayanamsa)\n";
echo "-----------------------------------\n";

$sweph->swe_set_sid_mode(SwissEphFFI::SE_SIDM_LAHIRI, 0.0, 0.0); // Lahiri

foreach (array_slice($planets, 0, 7) as $name => $id) {
    $result = $sweph->swe_calc_ut($julianDay, $id, SwissEphFFI::SEFLG_SIDEREAL, $xx, $serr);
    
    if ($result !== SwissEphFFI::ERR) {
        $longitude = $xx[0];
        $sign = floor($longitude / 30);
        $degree = $longitude - ($sign * 30);
        
        $signs = ['Ari', 'Tau', 'Gem', 'Can', 'Leo', 'Vir', 
                  'Lib', 'Sco', 'Sag', 'Cap', 'Aqu', 'Pis'];
        
        printf("%-12s: %3s %8.2f°\n",
            $name,
            $signs[(int)$sign],
            $degree
        );
    }
}

// Reset to tropical
$sweph->swe_set_sid_mode(0, 0.0, 0.0);

echo "\nDone!\n";
