<?php

declare(strict_types=1);

namespace SwissEph\Tests;

use PHPUnit\Framework\TestCase;
use SwissEph\FFI\SwissEphFFI;

/**
 * Test suite for SwissEphFFI
 * 
 * These tests verify that the FFI bindings work correctly
 * and produce accurate astronomical calculations.
 */
class SwissEphFFITest extends TestCase
{
    private ?SwissEphFFI $sweph = null;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Skip tests if library is not compiled
        if (!file_exists(__DIR__ . '/../build/libswe.so')) {
            $this->markTestSkipped(
                'Swiss Ephemeris library not compiled. Run: bash build/compile.sh'
            );
        }
        
        $this->sweph = new SwissEphFFI();
    }
    
    public function testVersion(): void
    {
        $version = $this->sweph->swe_version();
        
        $this->assertIsString($version);
        $this->assertMatchesRegularExpression('/^\d+\.\d+/', $version);
    }
    
    public function testJulianDayConversion(): void
    {
        // Test known Julian Day: January 1, 2000, 12:00 UT = 2451545.0
        $jd = $this->sweph->swe_julday(2000, 1, 1, 12.0);
        
        $this->assertEqualsWithDelta(2451545.0, $jd, 0.0001);
        
        // Test reverse conversion
        $date = $this->sweph->swe_revjul($jd);
        
        $this->assertEquals(2000, $date['year']);
        $this->assertEquals(1, $date['month']);
        $this->assertEquals(1, $date['day']);
        $this->assertEqualsWithDelta(12.0, $date['hour'], 0.0001);
    }
    
    public function testSunPosition(): void
    {
        // Calculate Sun position for January 1, 2000, 12:00 UT
        $jd = 2451545.0;
        $position = $this->sweph->swe_calc_ut($jd, SwissEphFFI::SE_SUN);
        
        $this->assertIsArray($position);
        $this->assertArrayHasKey('longitude', $position);
        $this->assertArrayHasKey('latitude', $position);
        $this->assertArrayHasKey('distance', $position);
        $this->assertArrayHasKey('longitude_speed', $position);
        
        // Sun should be around 280° (Capricorn) on January 1
        $this->assertGreaterThan(270, $position['longitude']);
        $this->assertLessThan(300, $position['longitude']);
        
        // Distance should be around 0.98 AU (Earth at perihelion)
        $this->assertGreaterThan(0.9, $position['distance']);
        $this->assertLessThan(1.1, $position['distance']);
    }
    
    public function testMoonPosition(): void
    {
        $jd = 2451545.0;
        $position = $this->sweph->swe_calc_ut($jd, SwissEphFFI::SE_MOON);
        
        $this->assertIsArray($position);
        $this->assertArrayHasKey('longitude', $position);
        $this->assertArrayHasKey('latitude', $position);
        
        // Moon latitude can vary up to ±5°
        $this->assertGreaterThan(-6, $position['latitude']);
        $this->assertLessThan(6, $position['latitude']);
        
        // Moon speed should be around 13°/day
        $this->assertGreaterThan(10, $position['longitude_speed']);
        $this->assertLessThan(16, $position['longitude_speed']);
    }
    
    public function testHouseCalculation(): void
    {
        $jd = 2451545.0;
        $latitude = 40.7128; // New York
        $longitude = -74.0060;
        
        $houses = $this->sweph->swe_houses($jd, $latitude, $longitude, 'P');
        
        $this->assertIsArray($houses);
        $this->assertArrayHasKey('cusps', $houses);
        $this->assertArrayHasKey('ascendant', $houses);
        $this->assertArrayHasKey('mc', $houses);
        
        // Should have 12 house cusps
        $this->assertCount(12, $houses['cusps']);
        
        // House cusps should be in ascending order (mostly)
        for ($i = 1; $i < 12; $i++) {
            $this->assertGreaterThan($houses['cusps'][$i] - 30, $houses['cusps'][$i + 1] ?? $houses['cusps'][1] + 360);
        }
    }
    
    public function testAyanamsa(): void
    {
        $jd = 2451545.0;
        $ayanamsa = $this->sweph->swe_get_ayanamsa_ut($jd);
        
        $this->assertIsFloat($ayanamsa);
        
        // For year 2000, ayanamsa should be around 23.85° (Lahiri)
        $this->assertGreaterThan(23.0, $ayanamsa);
        $this->assertLessThan(24.5, $ayanamsa);
    }
    
    public function testDeltaT(): void
    {
        $jd = 2451545.0;
        $deltat = $this->sweph->swe_deltat($jd);
        
        $this->assertIsFloat($deltat);
        
        // Delta T for year 2000 should be around 64 seconds (in days)
        $this->assertGreaterThan(0.0007, $deltat);
        $this->assertLessThan(0.0008, $deltat);
    }
    
    public function testSplitDegrees(): void
    {
        $degrees = 123.456789;
        $result = $this->sweph->swe_split_deg($degrees);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('degree', $result);
        $this->assertArrayHasKey('minute', $result);
        $this->assertArrayHasKey('second', $result);
        
        $this->assertEquals(3, $result['degree']); // 123 mod 30 = 3
        $this->assertEquals(27, $result['minute']);
        $this->assertGreaterThan(24, $result['second']);
    }
    
    public function testPlanetName(): void
    {
        $name = $this->sweph->swe_get_planet_name(SwissEphFFI::SE_SUN);
        
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }
    
    public function testObliquity(): void
    {
        $jd = 2451545.0;
        $obliquity = $this->sweph->swe_get_ecliptic_obliquity($jd);
        
        $this->assertIsFloat($obliquity);
        
        // Obliquity for J2000 should be around 23.44°
        $this->assertGreaterThan(23.4, $obliquity);
        $this->assertLessThan(23.5, $obliquity);
    }
    
    public function testNutation(): void
    {
        $jd = 2451545.0;
        $nutation = $this->sweph->swe_nutation($jd);
        
        $this->assertIsArray($nutation);
        $this->assertArrayHasKey('nutation_longitude', $nutation);
        $this->assertArrayHasKey('nutation_latitude', $nutation);
        $this->assertArrayHasKey('mean_obliquity', $nutation);
        $this->assertArrayHasKey('true_obliquity', $nutation);
    }
    
    public function testConstellation(): void
    {
        // Sun at 280° should be in Sagittarius (Sgr)
        $constellation = $this->sweph->swe_get_constellation([280.0, 0.0, 1.0]);
        
        $this->assertIsString($constellation);
        $this->assertNotEmpty($constellation);
    }
    
    public function testCoordinateTransformation(): void
    {
        $result = $this->sweph->swe_cotrans(0.0, 0.0, 1.0, 23.44);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('x', $result);
        $this->assertArrayHasKey('y', $result);
        $this->assertArrayHasKey('z', $result);
    }
    
    public function testRefraction(): void
    {
        // Test refraction at horizon (0° altitude)
        $refraction = $this->sweph->swe_refrac(0.0);
        
        $this->assertIsFloat($refraction);
        
        // Refraction at horizon should be around 34 arcminutes (0.57°)
        $this->assertGreaterThan(0.5, $refraction);
        $this->assertLessThan(0.6, $refraction);
    }
}
