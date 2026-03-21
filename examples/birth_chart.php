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

echo "Swiss Ephemeris Version: " . $sweph->swe_version() . "\n\n";

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
    $birthData['hour']
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

foreach ($planets as $name => $id) {
    $position = $sweph->swe_calc_ut($julianDay, $id);
    
    if ($position !== false) {
        // Convert to sign-degree format
        $sign = floor($position['longitude'] / 30);
        $degree = $position['longitude'] % 30;
        
        $signs = ['Ari', 'Tau', 'Gem', 'Can', 'Leo', 'Vir', 
                  'Lib', 'Sco', 'Sag', 'Cap', 'Aqu', 'Pis'];
        
        printf("%-12s: %3s %8.2f° (Speed: %+.4f°/day)\n",
            $name,
            $signs[$sign],
            $degree,
            $position['longitude_speed']
        );
    }
}

echo "\n";

// Calculate house cusps
echo "House Cusps (Placidus)\n";
echo "-----------------------\n";

$houses = $sweph->swe_houses(
    $julianDay,
    $birthData['latitude'],
    $birthData['longitude'],
    'P' // Placidus
);

if ($houses !== false) {
    foreach ($houses['cusps'] as $house => $longitude) {
        $sign = floor($longitude / 30);
        $degree = $longitude % 30;
        
        $signs = ['Ari', 'Tau', 'Gem', 'Can', 'Leo', 'Vir', 
                  'Lib', 'Sco', 'Sag', 'Cap', 'Aqu', 'Pis'];
        
        printf("House %2d: %3s %8.2f°\n", $house, $signs[$sign], $degree);
    }
    
    echo "\n";
    printf("Ascendant: %3s %8.2f°\n", 
        $signs[floor($houses['ascendant'] / 30)], 
        $houses['ascendant'] % 30
    );
    printf("Midheaven (MC): %3s %8.2f°\n", 
        $signs[floor($houses['mc'] / 30)], 
        $houses['mc'] % 30
    );
}

echo "\n";

// Get ayanamsa
$ayanamsa = $sweph->swe_get_ayanamsa_ut($julianDay);
echo "Ayanamsa (Lahiri): $ayanamsa°\n";

// Calculate sidereal positions
echo "\nSidereal Positions (with Ayanamsa)\n";
echo "-----------------------------------\n";

$sweph->swe_set_sid_mode(0); // Fagan/Bradley

foreach (array_slice($planets, 0, 7) as $name => $id) {
    $position = $sweph->swe_calc_ut($julianDay, $id, SwissEphFFI::SEFLG_SIDEREAL);
    
    if ($position !== false) {
        $sign = floor($position['longitude'] / 30);
        $degree = $position['longitude'] % 30;
        
        $signs = ['Ari', 'Tau', 'Gem', 'Can', 'Leo', 'Vir', 
                  'Lib', 'Sco', 'Sag', 'Cap', 'Aqu', 'Pis'];
        
        printf("%-12s: %3s %8.2f°\n",
            $name,
            $signs[$sign],
            $degree
        );
    }
}

// Reset to tropical
$sweph->swe_set_sid_mode(0);

echo "\nDone!\n";
