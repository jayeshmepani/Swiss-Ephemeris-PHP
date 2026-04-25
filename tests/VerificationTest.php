<?php

declare(strict_types=1);

namespace SwissEph\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use SwissEph\FFI\SwissEphFFI;

/** VerificationTest - Cross-validation against swetest CLI using decimal output. */
final class VerificationTest extends TestCase
{
    private ?SwissEphFFI $sweph = null;
    private string $swetestPath;
    private string $ephePath;

    protected function setUp(): void
    {
        parent::setUp();
        try {
            $this->sweph = new SwissEphFFI;
            $this->swetestPath = __DIR__ . '/../build/swisseph_src/bin/swetest';
            $this->ephePath = __DIR__ . '/../build/swisseph_src/ephe';
            $this->sweph->swe_set_ephe_path($this->ephePath);
            if (!file_exists($this->swetestPath)) { $this->markTestSkipped(); }
        } catch (RuntimeException) {
            $this->markTestSkipped();
        }
    }

    /** @dataProvider jdDataProvider */
    public function testPlanetParity(float $jd, int $ipl, string $name): void
    {
        $expected = $this->getSwetestValue($jd, $ipl);
        $xx = $this->sweph->getFFI()->new('double[6]');
        $serr = $this->sweph->getFFI()->new('char[256]');
        $this->sweph->swe_calc($jd, $ipl, SwissEphFFI::SEFLG_SPEED | SwissEphFFI::SEFLG_SWIEPH, $xx, $serr);
        $this->assertEqualsWithDelta($expected, $xx[0], 0.00001);
    }

    public function testHouseParity(): void
    {
        $jd = 2451545.0;
        $lat = 51.5074; $lon = -0.1278;

        // -fl gives raw decimals. Line 14 (index 13) is House 1.
        $cmd = sprintf('%s -bj%f -geopos%f,%f,0 -house -fl -head -ut -edir%s', escapeshellarg($this->swetestPath), $jd, $lon, $lat, escapeshellarg($this->ephePath));
        $output = shell_exec($cmd);
        $lines = explode("\n", trim($output));
        $expectedCusp1 = (float) ($lines[13] ?? 0);

        $cusps = $this->sweph->getFFI()->new('double[13]');
        $ascmc = $this->sweph->getFFI()->new('double[10]');
        $this->sweph->swe_houses($jd, $lat, $lon, ord('P'), $cusps, $ascmc);

        $this->assertGreaterThan(0, $expectedCusp1);
        $this->assertEqualsWithDelta($expectedCusp1, $cusps[1], 0.0001);
    }

    public function testEclipseParity(): void
    {
        $jd_start = 2460000.5;
        $tret = $this->sweph->getFFI()->new('double[10]');
        $serr = $this->sweph->getFFI()->new('char[256]');
        $this->sweph->swe_lun_eclipse_when($jd_start, SwissEphFFI::SEFLG_SWIEPH, 0, $tret, 0, $serr);

        $cmd = sprintf('%s -bj%f -lunecl -head -edir%s', escapeshellarg($this->swetestPath), $jd_start, escapeshellarg($this->ephePath));
        $output = shell_exec($cmd);
        // Verify the date string exists in output
        $this->assertStringContainsString(number_format($tret[0], 4, '.', ''), $output);
    }

    public static function jdDataProvider(): array
    {
        return [
            [2451545.0, 0, 'J2000 Sun'],
            [2461155.5, 1, '2026 Moon'],
        ];
    }

    private function getSwetestValue(float $jd, int $ipl): float
    {
        $cmd = sprintf('%s -p%d -bj%f -fl -head -edir%s', escapeshellarg($this->swetestPath), $ipl, $jd, escapeshellarg($this->ephePath));
        return (float) trim(shell_exec($cmd));
    }
}
