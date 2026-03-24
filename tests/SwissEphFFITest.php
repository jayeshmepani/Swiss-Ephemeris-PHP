<?php

declare(strict_types=1);

namespace SwissEph\Tests;

use FFI;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SwissEph\FFI\SwissEphFFI;

/**
 * Test suite for SwissEphFFI.
 *
 * Verifies FFI bindings functionality and calculation accuracy.
 * All tests compare against known astronomical values from:
 * - NASA Horizons system
 * - Swiss Ephemeris test suite (swetest)
 * - Astrodienst reference data
 *
 * @author Jayesh Patel <jayeshmepani777@gmail.com>
 *
 * @see \SwissEph\FFI\SwissEphFFI
 * @see https://www.astro.com/swisseph/ Swiss Ephemeris Official Site
 */
final class SwissEphFFITest extends TestCase
{
    /** Swiss Ephemeris FFI instance */
    private ?SwissEphFFI $sweph = null;

    /**
     * Set up test fixture.
     *
     * Initializes SwissEphFFI instance.
     * Skips tests if library is not compiled.
     */
    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->sweph = new SwissEphFFI;
        } catch (RuntimeException $e) {
            $this->markTestSkipped('Swiss Ephemeris library not found: ' . $e->getMessage());
        }
    }

    /**
     * Test library version retrieval.
     *
     * Verifies swe_version() returns valid version string.
     */
    public function testVersion(): void
    {
        $versionStr = $this->sweph->getFFI()->new('char[256]');
        $this->sweph->swe_version($versionStr);
        $version = FFI::string($versionStr);

        $this->assertIsString($version);
        $this->assertMatchesRegularExpression('/^\d+\.\d+/', $version);
    }

    /**
     * Test Julian Day conversion.
     *
     * Verifies swe_julday() and swe_revjul() accuracy.
     * Reference: January 1, 2000, 12:00 UT = JD 2451545.0 (J2000 epoch)
     */
    public function testJulianDayConversion(): void
    {
        // Test forward conversion (date → JD)
        $jd = $this->sweph->swe_julday(2000, 1, 1, 12.0, SwissEphFFI::SE_GREG_CAL);
        $this->assertEqualsWithDelta(2451545.0, $jd, 0.0001);

        // Test reverse conversion (JD → date)
        $year = $this->sweph->getFFI()->new('int32[1]');
        $month = $this->sweph->getFFI()->new('int32[1]');
        $day = $this->sweph->getFFI()->new('int32[1]');
        $hour = $this->sweph->getFFI()->new('double[1]');

        $this->sweph->swe_revjul($jd, SwissEphFFI::SE_GREG_CAL, $year, $month, $day, $hour);

        $this->assertEquals(2000, $year[0]);
        $this->assertEquals(1, $month[0]);
        $this->assertEquals(1, $day[0]);
        $this->assertEqualsWithDelta(12.0, $hour[0], 0.0001);
    }

    /**
     * Test Sun position calculation.
     *
     * Verifies swe_calc_ut() for Sun at J2000 epoch.
     * Expected: Sun at ~280° (Capricorn) on January 1, 2000.
     */
    public function testSunPosition(): void
    {
        $jd = 2451545.0;
        $xx = $this->sweph->getFFI()->new('double[6]');
        $serr = $this->sweph->getFFI()->new('char[256]');
        $result = $this->sweph->swe_calc_ut($jd, 0, SwissEphFFI::SEFLG_SPEED, $xx, $serr);

        $this->assertGreaterThanOrEqual(0, $result);

        // Sun longitude should be ~280° (Capricorn)
        $this->assertGreaterThan(270, $xx[0]);
        $this->assertLessThan(300, $xx[0]);

        // Earth-Sun distance should be ~0.98 AU (perihelion in January)
        $this->assertGreaterThan(0.9, $xx[2]);
        $this->assertLessThan(1.1, $xx[2]);
    }

    /**
     * Test Moon position calculation.
     *
     * Verifies swe_calc_ut() for Moon at J2000 epoch.
     * Moon latitude varies ±5° due to orbital inclination.
     * Moon speed averages ~13°/day.
     */
    public function testMoonPosition(): void
    {
        $jd = 2451545.0;
        $xx = $this->sweph->getFFI()->new('double[6]');
        $serr = $this->sweph->getFFI()->new('char[256]');
        $result = $this->sweph->swe_calc_ut($jd, 1, SwissEphFFI::SEFLG_SPEED, $xx, $serr);

        $this->assertGreaterThanOrEqual(0, $result);

        // Moon latitude can vary up to ±5°
        $this->assertGreaterThan(-6, $xx[1]);
        $this->assertLessThan(6, $xx[1]);

        // Moon speed should be around 13°/day
        $this->assertGreaterThan(10, $xx[3]);
        $this->assertLessThan(16, $xx[3]);
    }

    /**
     * Test house cusp calculation.
     *
     * Verifies swe_houses() for Placidus house system.
     */
    public function testHouseCalculation(): void
    {
        $jd = 2451545.0;
        $latitude = 40.7128;  // New York
        $longitude = -74.0060;

        $cusps = $this->sweph->getFFI()->new('double[13]');
        $ascmc = $this->sweph->getFFI()->new('double[10]');

        $result = $this->sweph->swe_houses($jd, $latitude, $longitude, ord(SwissEphFFI::SE_HOUSES_PLACIDUS), $cusps, $ascmc);

        $this->assertGreaterThanOrEqual(0, $result);
    }

    /**
     * Test ayanamsa calculation.
     *
     * Verifies swe_get_ayanamsa_ut() for Lahiri ayanamsa.
     * Year 2000: ~23.85°, increases ~50 arcseconds/year.
     */
    public function testAyanamsa(): void
    {
        $jd = 2451545.0;
        $this->sweph->swe_set_sid_mode(SwissEphFFI::SE_SIDM_LAHIRI, 0.0, 0.0);
        $ayanamsa = $this->sweph->swe_get_ayanamsa_ut($jd);

        $this->assertIsFloat($ayanamsa);

        // For year 2000, ayanamsa should be around 23.85° (Lahiri)
        $this->assertGreaterThan(23.0, $ayanamsa);
        $this->assertLessThan(24.5, $ayanamsa);
    }

    /**
     * Test Delta T calculation.
     *
     * Verifies swe_deltat() - difference between Ephemeris Time and Universal Time.
     * Year 2000: ~64 seconds = 0.00074 days.
     */
    public function testDeltaT(): void
    {
        $jd = 2451545.0;
        $deltat = $this->sweph->swe_deltat($jd);

        $this->assertIsFloat($deltat);

        // Delta T for year 2000 should be around 64 seconds (in days)
        $this->assertGreaterThan(0.0007, $deltat);
        $this->assertLessThan(0.0008, $deltat);
    }

    /**
     * Test degree splitting.
     *
     * Verifies swe_split_deg() converts decimal degrees to DMS format.
     */
    public function testSplitDegrees(): void
    {
        $degrees = 123.456789;
        $ideg = $this->sweph->getFFI()->new('int32[1]');
        $imin = $this->sweph->getFFI()->new('int32[1]');
        $isec = $this->sweph->getFFI()->new('int32[1]');
        $dsecfr = $this->sweph->getFFI()->new('double[1]');
        $isgn = $this->sweph->getFFI()->new('int32[1]');

        $this->sweph->swe_split_deg($degrees, 0, $ideg, $imin, $isec, $dsecfr, $isgn);

        $this->assertEquals(123, $ideg[0]);
        $this->assertEquals(27, $imin[0]);  // 0.456789 * 60 = 27.40734
        $this->assertGreaterThanOrEqual(24, $isec[0]);  // 0.40734 * 60 = 24.4404
    }

    /**
     * Test planet name retrieval.
     *
     * Verifies swe_get_planet_name() returns valid name.
     */
    public function testPlanetName(): void
    {
        $nameStr = $this->sweph->getFFI()->new('char[256]');
        $this->sweph->swe_get_planet_name(0, $nameStr);
        $name = FFI::string($nameStr);

        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    /**
     * Test atmospheric refraction.
     *
     * Verifies swe_refrac() calculates refraction at horizon.
     * Standard refraction at horizon: ~34 arcminutes (0.57°).
     */
    public function testRefraction(): void
    {
        $refraction = $this->sweph->swe_refrac(0.0, 1013.25, 15.0, 0);

        $this->assertIsFloat($refraction);

        // Refraction at horizon should be around 34 arcminutes (0.57°)
        $this->assertGreaterThan(0.4, $refraction);
        $this->assertLessThan(0.6, $refraction);
    }
}
