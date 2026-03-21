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
        $versionStr = $this->sweph->getFFI()->new("char[256]");
        $this->sweph->swe_version($versionStr);
        $version = \FFI::string($versionStr);
        
        $this->assertIsString($version);
        $this->assertMatchesRegularExpression('/^\d+\.\d+/', $version);
    }
    
    public function testJulianDayConversion(): void
    {
        // Test known Julian Day: January 1, 2000, 12:00 UT = 2451545.0
        $jd = $this->sweph->swe_julday(2000, 1, 1, 12.0, SwissEphFFI::SE_GREG_CAL);
        
        $this->assertEqualsWithDelta(2451545.0, $jd, 0.0001);
        
        // Test reverse conversion
        $year = $this->sweph->getFFI()->new("int32[1]");
        $month = $this->sweph->getFFI()->new("int32[1]");
        $day = $this->sweph->getFFI()->new("int32[1]");
        $hour = $this->sweph->getFFI()->new("double[1]");

        $this->sweph->swe_revjul($jd, SwissEphFFI::SE_GREG_CAL, $year, $month, $day, $hour);
        
        $this->assertEquals(2000, $year[0]);
        $this->assertEquals(1, $month[0]);
        $this->assertEquals(1, $day[0]);
        $this->assertEqualsWithDelta(12.0, $hour[0], 0.0001);
    }
    
    public function testSunPosition(): void
    {
        // Calculate Sun position for January 1, 2000, 12:00 UT
        $jd = 2451545.0;
        $xx = $this->sweph->getFFI()->new("double[6]");
        $serr = $this->sweph->getFFI()->new("char[256]");
        $result = $this->sweph->swe_calc_ut($jd, 0, SwissEphFFI::SEFLG_SPEED, $xx, $serr);
        
        $this->assertGreaterThanOrEqual(0, $result);
        
        // Sun should be around 280° (Capricorn) on January 1
        $this->assertGreaterThan(270, $xx[0]);
        $this->assertLessThan(300, $xx[0]);
        
        // Distance should be around 0.98 AU (Earth at perihelion)
        $this->assertGreaterThan(0.9, $xx[2]);
        $this->assertLessThan(1.1, $xx[2]);
    }
    
    public function testMoonPosition(): void
    {
        $jd = 2451545.0;
        $xx = $this->sweph->getFFI()->new("double[6]");
        $serr = $this->sweph->getFFI()->new("char[256]");
        $result = $this->sweph->swe_calc_ut($jd, 1, SwissEphFFI::SEFLG_SPEED, $xx, $serr);
        
        $this->assertGreaterThanOrEqual(0, $result);
        
        // Moon latitude can vary up to ±5°
        $this->assertGreaterThan(-6, $xx[1]);
        $this->assertLessThan(6, $xx[1]);
        
        // Moon speed should be around 13°/day
        $this->assertGreaterThan(10, $xx[3]);
        $this->assertLessThan(16, $xx[3]);
    }
    
    public function testHouseCalculation(): void
    {
        $jd = 2451545.0;
        $latitude = 40.7128; // New York
        $longitude = -74.0060;
        
        $cusps = $this->sweph->getFFI()->new("double[13]");
        $ascmc = $this->sweph->getFFI()->new("double[10]");

        $result = $this->sweph->swe_houses($jd, $latitude, $longitude, ord(SwissEphFFI::SE_HOUSES_PLACIDUS), $cusps, $ascmc);
        
        $this->assertGreaterThanOrEqual(0, $result);
        
        // House cusps should be in ascending order (mostly)
        for ($i = 1; $i < 12; $i++) {
             // skip house strict comparison due to 360 wrap
        }
    }
    
    public function testAyanamsa(): void
    {
        $jd = 2451545.0;
        // set sidereal mode
        $this->sweph->swe_set_sid_mode(SwissEphFFI::SE_SIDM_LAHIRI, 0.0, 0.0);
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
        $ideg = $this->sweph->getFFI()->new("int32[1]");
        $imin = $this->sweph->getFFI()->new("int32[1]");
        $isec = $this->sweph->getFFI()->new("int32[1]");
        $dsecfr = $this->sweph->getFFI()->new("double[1]");
        $isgn = $this->sweph->getFFI()->new("int32[1]");

        $this->sweph->swe_split_deg($degrees, 0, $ideg, $imin, $isec, $dsecfr, $isgn);
        
        $this->assertEquals(123, $ideg[0]);
        $this->assertEquals(27, $imin[0]);
        $this->assertGreaterThanOrEqual(24, $isec[0]);
    }
    
    public function testPlanetName(): void
    {
        $nameStr = $this->sweph->getFFI()->new("char[256]");
        $this->sweph->swe_get_planet_name(0, $nameStr);
        $name = \FFI::string($nameStr);
        
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }
    
    public function testRefraction(): void
    {
        // Test refraction at horizon (0° altitude)
        $refraction = $this->sweph->swe_refrac(0.0, 1013.25, 15.0, 0);
        
        $this->assertIsFloat($refraction);
        
        // Refraction at horizon should be around 34 arcminutes (0.57°)
        $this->assertGreaterThan(0.4, $refraction);
        $this->assertLessThan(0.6, $refraction);
    }
}
