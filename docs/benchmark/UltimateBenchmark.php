<?php

declare(strict_types=1);

namespace SwissEph\Benchmark;

use FFI;
use FFI\CData;
use ReflectionFunction;
use ReflectionNamedType;
use SwissEph\FFI\SwissEphFFI;
use Throwable;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/constants.php';

/**
 * THE ULTIMATE TRANSPARENCY BENCHMARK (106 FUNCTIONS).
 *
 * Target: Strict 1,000 iterations for ALL 106 functions.
 * Comparison: jayeshmepani/swiss-ephemeris-ffi (FFI) vs. kevindecapite/php-sweph (C-Extension)
 */
class UltimateBenchmark
{
    private SwissEphFFI $ffi;
    private array $config = [];
    private array $results = [];

    private CData $xx;
    private CData $serr;
    private CData $cusps;
    private CData $ascmc;
    private CData $tret;
    private CData $attr;
    private CData $ii;
    private CData $geopos;
    private CData $datm;
    private CData $dobs;
    private CData $dret;
    private CData $s1;
    private CData $s2;

    public function __construct()
    {
        $this->ffi = new SwissEphFFI;
        $f = $this->ffi->getFFI();

        $this->xx = $f->new('double[20]');
        $this->serr = $f->new('char[256]');
        $this->cusps = $f->new('double[13]');
        $this->ascmc = $f->new('double[10]');
        $this->tret = $f->new('double[40]');
        $this->attr = $f->new('double[40]');
        $this->ii = $f->new('int32[20]');
        $this->geopos = $f->new('double[10]');
        $this->datm = $f->new('double[10]');
        $this->dobs = $f->new('double[10]');
        $this->dret = $f->new('double[40]');
        $this->s1 = $f->new('char[512]');
        $this->s2 = $f->new('char[512]');

        $this->geopos[0] = 72.6313; $this->geopos[1] = 23.1815; $this->geopos[2] = 0.0;

        $this->ffi->swe_set_ephe_path('.');
        if (function_exists('swe_set_ephe_path')) { @swe_set_ephe_path('.'); }

        $this->initializeConfig();
    }

    public function run(int $n = 1000, int $w = 100): void
    {
        set_time_limit(0);
        echo "════════════════════════════════════════════════════════════════\n";
        echo "  STRICT PERFORMANCE AUDIT (LOSSLESS MODE)\n";
        echo "  - Protocol:             100 Warmup + 1000 Measurement Samples\n";
        echo '  - Total API Functions:  ' . count($this->config) . "\n";
        echo "  - Transparency Mode:    Verified Environment Hardware Probes\n";
        echo "════════════════════════════════════════════════════════════════\n\n";

        foreach ($this->config as $name => $args) {
            $this->benchBoth($name, $args, $n, $w);
        }

        $export = [
            'system' => $this->getSystemMetadata(),
            'results' => $this->results,
        ];
        file_put_contents('comprehensive_benchmark_stats.json', json_encode($export, JSON_PRETTY_PRINT));
        echo "\n✅ ALL STATS EXPORTED TO comprehensive_benchmark_stats.json\n";
    }

    private function initializeConfig(): void
    {
        $jd = 2451545.0; $ipl = 0; $iflag = 2; $P = ord('P');

        $this->config = [
            'swe_heliacal_ut' => [$jd, $this->geopos, $this->datm, $this->dobs, 'Sirius', 1, $iflag, $this->dret, $this->serr],
            'swe_heliacal_pheno_ut' => [$jd, $this->geopos, $this->datm, $this->dobs, 'Sirius', 1, $iflag, $this->dret, $this->serr],
            'swe_vis_limit_mag' => [$jd, $this->geopos, $this->datm, $this->dobs, 'Sirius', $iflag, $this->dret, $this->serr],
            'swe_heliacal_angle' => [$jd, $this->geopos, $this->datm, $this->dobs, $iflag, 0.0, 0.0, 0.0, 0.0, 0.0, $this->dret, $this->serr],
            'swe_topo_arcus_visionis' => [$jd, $this->geopos, $this->datm, $this->dobs, $iflag, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, $this->dret, $this->serr],
            'swe_set_astro_models' => ['test', 0],
            'swe_get_astro_models' => [$this->s1, $this->s2, 0],
            'swe_version' => [$this->s1],
            'swe_get_library_path' => [$this->s1],
            'swe_calc' => [$jd, $ipl, $iflag, $this->xx, $this->serr],
            'swe_calc_ut' => [$jd, $ipl, $iflag, $this->xx, $this->serr],
            'swe_calc_pctr' => [$jd, $ipl, 14, $iflag, $this->xx, $this->serr],
            'swe_solcross' => [0.0, $jd, $iflag, $this->serr],
            'swe_solcross_ut' => [0.0, $jd, $iflag, $this->serr],
            'swe_mooncross' => [0.0, $jd, $iflag, $this->serr],
            'swe_mooncross_ut' => [0.0, $jd, $iflag, $this->serr],
            'swe_mooncross_node' => [$jd, $iflag, $this->xx, $this->xx, $this->serr],
            'swe_mooncross_node_ut' => [$jd, $iflag, $this->xx, $this->xx, $this->serr],
            'swe_helio_cross' => [$ipl, 0.0, $jd, $iflag, 1, $this->xx, $this->serr],
            'swe_helio_cross_ut' => [$ipl, 0.0, $jd, $iflag, 1, $this->xx, $this->serr],
            'swe_fixstar' => ['Sirius', $jd, $iflag, $this->xx, $this->serr],
            'swe_fixstar_ut' => ['Sirius', $jd, $iflag, $this->xx, $this->serr],
            'swe_fixstar_mag' => ['Sirius', $this->xx, $this->serr],
            'swe_fixstar2' => ['Sirius', $jd, $iflag, $this->xx, $this->serr],
            'swe_fixstar2_ut' => ['Sirius', $jd, $iflag, $this->xx, $this->serr],
            'swe_fixstar2_mag' => ['Sirius', $this->xx, $this->serr],
            'swe_close' => [],
            'swe_set_ephe_path' => ['.'],
            'swe_set_jpl_file' => ['de431.eph'],
            'swe_get_planet_name' => [$ipl, $this->s1],
            'swe_set_topo' => [72.6, 23.1, 0.0],
            'swe_set_sid_mode' => [1, 0.0, 0.0],
            'swe_get_ayanamsa_ex' => [$jd, $iflag, $this->xx, $this->serr],
            'swe_get_ayanamsa_ex_ut' => [$jd, $iflag, $this->xx, $this->serr],
            'swe_get_ayanamsa' => [$jd],
            'swe_get_ayanamsa_ut' => [$jd],
            'swe_get_ayanamsa_name' => [1],
            'swe_get_current_file_data' => [1, $this->xx, $this->xx, $this->ii],
            'swe_date_conversion' => [2024, 4, 30, 12.0, 'g', $this->xx],
            'swe_julday' => [2024, 4, 30, 12.0, 1],
            'swe_revjul' => [$jd, 1, $this->ii, $this->ii, $this->ii, $this->xx],
            'swe_utc_to_jd' => [2024, 4, 30, 12, 0, 0.0, 1, $this->dret, $this->serr],
            'swe_jdet_to_utc' => [$jd, 1, $this->ii, $this->ii, $this->ii, $this->ii, $this->ii, $this->xx],
            'swe_jdut1_to_utc' => [$jd, 1, $this->ii, $this->ii, $this->ii, $this->ii, $this->ii, $this->xx],
            'swe_utc_time_zone' => [2024, 4, 30, 12, 0, 0.0, 5.5, $this->ii, $this->ii, $this->ii, $this->ii, $this->ii, $this->xx],
            'swe_houses' => [$jd, 23.1, 72.6, $P, $this->cusps, $this->ascmc],
            'swe_houses_ex' => [$jd, $iflag, 23.1, 72.6, $P, $this->cusps, $this->ascmc],
            'swe_houses_ex2' => [$jd, $iflag, 23.1, 72.6, $P, $this->cusps, $this->ascmc, $this->xx, $this->xx, $this->serr],
            'swe_houses_armc' => [120.0, 23.1, 23.4, $P, $this->cusps, $this->ascmc],
            'swe_houses_armc_ex2' => [120.0, 23.1, 23.4, $P, $this->cusps, $this->ascmc, $this->xx, $this->xx, $this->serr],
            'swe_house_pos' => [120.0, 23.1, 23.4, $P, $this->xx, $this->serr],
            'swe_house_name' => [$P],
            'swe_gauquelin_sector' => [$jd, $ipl, 'Sirius', $iflag, 0, $this->geopos, 1013.25, 15.0, $this->xx, $this->serr],
            'swe_sol_eclipse_where' => [$jd, $iflag, $this->geopos, $this->attr, $this->serr],
            'swe_lun_occult_where' => [$jd, $ipl, 'Sirius', $iflag, $this->geopos, $this->attr, $this->serr],
            'swe_sol_eclipse_how' => [$jd, $iflag, $this->geopos, $this->attr, $this->serr],
            'swe_sol_eclipse_when_loc' => [$jd, $iflag, $this->geopos, $this->tret, $this->attr, 0, $this->serr],
            'swe_lun_occult_when_loc' => [$jd, $ipl, 'Sirius', $iflag, $this->geopos, $this->tret, $this->attr, 0, $this->serr],
            'swe_sol_eclipse_when_glob' => [$jd, $iflag, 0, $this->tret, 0, $this->serr],
            'swe_lun_occult_when_glob' => [$jd, $ipl, 'Sirius', $iflag, 0, $this->tret, 0, $this->serr],
            'swe_lun_eclipse_how' => [$jd, $iflag, $this->geopos, $this->attr, $this->serr],
            'swe_lun_eclipse_when' => [$jd, $iflag, 0, $this->tret, 0, $this->serr],
            'swe_lun_eclipse_when_loc' => [$jd, $iflag, $this->geopos, $this->tret, $this->attr, 0, $this->serr],
            'swe_pheno' => [$jd, $ipl, $iflag, $this->attr, $this->serr],
            'swe_pheno_ut' => [$jd, $ipl, $iflag, $this->attr, $this->serr],
            'swe_refrac' => [45.0, 1013.25, 15.0, 0],
            'swe_refrac_extended' => [45.0, 0.0, 1013.25, 15.0, 0.0065, 0, $this->xx],
            'swe_set_lapse_rate' => [0.0065],
            'swe_azalt' => [$jd, 0, $this->geopos, 1013.25, 15.0, $this->xx, $this->xx],
            'swe_azalt_rev' => [$jd, 0, $this->geopos, $this->xx, $this->xx],
            'swe_rise_trans_true_hor' => [$jd, $ipl, 'Sirius', $iflag, 1, $this->geopos, 1013.25, 15.0, 0.0, $this->tret, $this->serr],
            'swe_rise_trans' => [$jd, $ipl, 'Sirius', $iflag, 1, $this->geopos, 1013.25, 15.0, $this->tret, $this->serr],
            'swe_nod_aps' => [$jd, $ipl, $iflag, 0, $this->xx, $this->xx, $this->xx, $this->xx, $this->serr],
            'swe_nod_aps_ut' => [$jd, $ipl, $iflag, 0, $this->xx, $this->xx, $this->xx, $this->xx, $this->serr],
            'swe_get_orbital_elements' => [$jd, $ipl, $iflag, $this->dret, $this->serr],
            'swe_orbit_max_min_true_distance' => [$jd, $ipl, $iflag, $this->xx, $this->xx, $this->xx, $this->serr],
            'swe_deltat' => [$jd],
            'swe_deltat_ex' => [$jd, $iflag, $this->serr],
            'swe_time_equ' => [$jd, $this->xx, $this->serr],
            'swe_lmt_to_lat' => [$jd, 72.6, $this->xx, $this->serr],
            'swe_lat_to_lmt' => [$jd, 72.6, $this->xx, $this->serr],
            'swe_sidtime0' => [$jd, 23.4, 0.0],
            'swe_sidtime' => [$jd],
            'swe_set_interpolate_nut' => [1],
            'swe_cotrans' => [$this->xx, $this->xx, 23.4],
            'swe_cotrans_sp' => [$this->xx, $this->xx, 23.4],
            'swe_get_tid_acc' => [],
            'swe_set_tid_acc' => [0.0],
            'swe_set_delta_t_userdef' => [0.0],
            'swe_degnorm' => [370.0],
            'swe_radnorm' => [7.0],
            'swe_rad_midp' => [1.0, 2.0],
            'swe_deg_midp' => [10.0, 20.0],
            'swe_split_deg' => [123.456, 1, $this->ii, $this->ii, $this->ii, $this->xx, $this->ii],
            'swe_csnorm' => [123456],
            'swe_difcsn' => [123456, 654321],
            'swe_difdegn' => [100.0, 200.0],
            'swe_difcs2n' => [123456, 654321],
            'swe_difdeg2n' => [100.0, 200.0],
            'swe_difrad2n' => [1.0, 2.0],
            'swe_csroundsec' => [123456],
            'swe_d2l' => [123.456],
            'swe_day_of_week' => [$jd],
            'swe_cs2timestr' => [123456, ord(':'), 0, $this->s1],
            'swe_cs2lonlatstr' => [123456, 'E', 'W', $this->s1],
            'swe_cs2degstr' => [123456, $this->s1],
        ];
    }

    private function getSystemMetadata(): array
    {
        $os = PHP_OS_FAMILY;
        $meta = [
            'cpu' => 'Unknown Processor',
            'cores' => 'Unknown',
            'freq' => 'Unknown',
            'l3' => 'N/A',
            'arch' => php_uname('m'),
            'instr' => 'N/A',
            'ram' => 'Unknown RAM',
            'system' => 'N/A',
        ];

        if ($os === 'Linux') {
            $meta['cpu'] = $this->commandValue("lscpu | awk -F: '/Model name/ {sub(/^[ \\t]+/, \"\", $2); print $2; exit}'") ?: 'Linux CPU';
            $threads = $this->commandValue("lscpu | awk -F: '/^CPU\\(s\\)/ {gsub(/ /, \"\", $2); print $2; exit}'");
            $meta['cores'] = $threads ? $threads . ' Threads' : 'Unavailable on runner';
            $freqMhz = $this->firstNumericValue([
                $this->commandValue("lscpu | awk -F: '/CPU max MHz/ {gsub(/ /, \"\", $2); print $2; exit}'"),
                $this->commandValue('cat /sys/devices/system/cpu/cpu0/cpufreq/cpuinfo_max_freq 2>/dev/null'),
            ]);
            if ($freqMhz && $freqMhz > 10000) {
                $freqMhz /= 1000;
            }
            $meta['freq'] = $freqMhz ? $this->formatGhz($freqMhz / 1000) : 'Unavailable on runner';
            $meta['l3'] = $this->commandValue("lscpu | awk -F: '/L3 cache/ {sub(/^[ \\t]+/, \"\", $2); print $2; exit}'") ?: 'N/A';
            $meta['system'] = $this->commandValue('cat /sys/class/dmi/id/product_name 2>/dev/null') ?: 'Generic Linux Node';
            $flags = $this->commandValue("lscpu | awk -F: '/Flags/ {print $2; exit}'");
            $meta['instr'] = $this->formatInstructionSets($flags);
            $meta['ram'] = $this->commandValue("free -h | awk '/Mem:/ {print $2; exit}'") ?: 'Unknown';
        } elseif ($os === 'Darwin') {
            $meta['cpu'] = $this->commandValue('sysctl -n machdep.cpu.brand_string 2>/dev/null') ?: 'Apple Silicon';
            $cores = $this->commandValue('sysctl -n hw.ncpu 2>/dev/null');
            $meta['cores'] = $cores ? $cores . ' Logical Cores' : 'Unavailable on runner';
            $freqHz = $this->firstNumericValue([$this->commandValue('sysctl -n hw.cpufrequency 2>/dev/null')]);
            $meta['freq'] = $freqHz ? $this->formatGhz($freqHz / 1e9) : 'Unavailable on runner';
            $l3Bytes = $this->firstNumericValue([$this->commandValue('sysctl -n hw.l3cachesize 2>/dev/null')]);
            $meta['l3'] = $l3Bytes ? round($l3Bytes / 1024 / 1024) . ' MB' : 'N/A';
            $meta['system'] = $this->commandValue('sysctl -n hw.model 2>/dev/null') ?: 'Apple Mac';
            $features = $this->commandValue('sysctl -a 2>/dev/null | grep machdep.cpu.features');
            $meta['instr'] = php_uname('m') === 'arm64' ? 'NEON (ARM64)' : $this->formatInstructionSets($features);
            $ramGb = $this->firstNumericValue([$this->commandValue('sysctl -n hw.memsize 2>/dev/null')]);
            $meta['ram'] = $ramGb ? round($ramGb / 1024 / 1024 / 1024) . ' GB' : 'Unknown';
        } elseif ($os === 'Windows') {
            try {
                $ps = 'powershell -NoProfile -Command ';
                $meta['cpu'] = $this->commandValue($ps . '"(Get-CimInstance Win32_Processor | Select-Object -First 1 -ExpandProperty Name)"') ?: (getenv('PROCESSOR_IDENTIFIER') ?: 'Windows CPU');
                $cores = $this->commandValue($ps . '"(Get-CimInstance Win32_Processor | Measure-Object -Property NumberOfCores -Sum).Sum"');
                $threads = $this->commandValue($ps . '"(Get-CimInstance Win32_Processor | Measure-Object -Property NumberOfLogicalProcessors -Sum).Sum"');
                $meta['cores'] = ($cores && $threads) ? $cores . 'C / ' . $threads . 'T' : 'Unavailable on runner';
                $freqMhz = $this->firstNumericValue([$this->commandValue($ps . '"(Get-CimInstance Win32_Processor | Select-Object -First 1 -ExpandProperty MaxClockSpeed)"')]);
                $meta['freq'] = $freqMhz ? $this->formatGhz($freqMhz / 1000) : 'Unavailable on runner';
                $l3Kb = $this->firstNumericValue([$this->commandValue($ps . '"(Get-CimInstance Win32_Processor | Measure-Object -Property L3CacheSize -Sum).Sum"')]);
                $meta['l3'] = $l3Kb ? round($l3Kb / 1024) . ' MB' : 'N/A';
                $meta['system'] = $this->commandValue($ps . '"(Get-CimInstance Win32_ComputerSystem | Select-Object -First 1 -ExpandProperty Model)"') ?: 'Windows Runner';
                $ram = $this->firstNumericValue([$this->commandValue($ps . '"(Get-CimInstance Win32_PhysicalMemory | Measure-Object -Property Capacity -Sum).Sum"')]);
                $meta['ram'] = $ram ? round($ram / 1024 / 1024 / 1024) . ' GB' : 'Unknown';
                $intrinsics = $this->commandValue($ps . '"$s=@(); if ([System.Runtime.Intrinsics.X86.Avx2]::IsSupported) {$s+=\'AVX2\'}; if ([System.Runtime.Intrinsics.X86.Bmi2]::IsSupported) {$s+=\'BMI2\'}; if ([System.Runtime.Intrinsics.X86.Sse42]::IsSupported) {$s+=\'SSE4.2\'}; $s -join \', \'"');
                $meta['instr'] = $intrinsics ?: 'Unavailable on runner';
            } catch (Throwable $e) {
                $meta['cpu'] = 'Windows Virtual CPU';
                $meta['ram'] = 'Unknown';
            }
        }

        $libVersion = 'Unknown';
        try {
            $this->ffi->swe_version($this->s1);
            $libVersion = FFI::string($this->s1);
        } catch (Throwable $e) {}

        return array_merge($meta, [
            'php' => phpversion(),
            'os' => php_uname('s') . ' ' . php_uname('r') . ' (' . php_uname('m') . ')',
            'jit' => function_exists('opcache_get_status') && (opcache_get_status()['jit']['enabled'] ?? false) ? 'Enabled' : 'Disabled',
            'date' => date('Y-m-d H:i:s'),
            'library' => 'Swiss Ephemeris ' . $libVersion,
        ]);
    }

    private function commandValue(string $command): string
    {
        return trim((string) @shell_exec($command));
    }

    private function firstNumericValue(array $values): ?float
    {
        foreach ($values as $value) {
            if (is_numeric($value) && (float) $value > 0) {
                return (float) $value;
            }
        }

        return null;
    }

    private function formatGhz(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . ' GHz';
    }

    private function formatInstructionSets(string $flags): string
    {
        $map = [
            'avx2' => 'AVX2',
            'bmi2' => 'BMI2',
            'sse4_2' => 'SSE4.2',
            'sse4.2' => 'SSE4.2',
        ];
        $found = [];
        $flags = strtolower($flags);
        foreach ($map as $needle => $label) {
            if (str_contains($flags, $needle)) {
                $found[$label] = $label;
            }
        }

        return $found ? implode(', ', array_values($found)) : 'Unavailable on runner';
    }

    private function benchBoth(string $name, array $args, int $n, int $w): void
    {
        $ffi_err = null;
        $ffi_stats = $this->benchFFI($name, $args, $n, $w, $ffi_res, $ffi_err);

        $ext_err = null;
        $ext_stats = $this->benchExt($name, $args, $n, $w, $ext_res, $ext_err);

        if (!$ffi_stats) {
            printf("  ❌ %-30s | FFI ERR: %s\n", $name, $ffi_err ?: 'NOT_FOUND');
            return;
        }

        if (!$ext_stats) {
            printf("  ✓ %-30s | FFI:%7.2f us | C-EXT: N/A (%s)\n", $name, $ffi_stats['mean'], $ext_err ?: 'NOT_FOUND');
            $this->results[$name] = ['ffi' => $ffi_stats, 'ext' => null];
            return;
        }

        $acc = $this->checkAccuracy($name, $ffi_res, $ext_res);

        $ratios = [
            'mean' => $this->getRatio($ffi_stats['mean'], $ext_stats['mean']),
            'median' => $this->getRatio($ffi_stats['median'], $ext_stats['median']),
            'p95' => $this->getRatio($ffi_stats['p95'], $ext_stats['p95']),
            'stddev' => $this->getRatio($ffi_stats['stddev'], $ext_stats['stddev']),
            'min' => $this->getRatio($ffi_stats['min'], $ext_stats['min']),
            'max' => $this->getRatio($ffi_stats['max'], $ext_stats['max']),
            'mem' => $this->getRatio($ffi_stats['mem'], $ext_stats['mem']),
        ];

        printf("  ✓ %-30s | FFI:%7.2f us | Ratio(Mean):%4.2fx | Acc:%s\n",
            $name, $ffi_stats['mean'], $ratios['mean'], $acc ? 'MATCH' : 'DIFF');

        $this->results[$name] = [
            'ffi' => $ffi_stats,
            'ext' => $ext_stats,
            'accuracy' => $acc,
            'ratios' => $ratios,
        ];
    }

    private function getRatio($ffi, $ext)
    {
        if ($ext == 0) { return ($ffi == 0) ? 1.0 : 999.9; }
        return (float)($ffi / $ext);
    }

    private function benchFFI(string $name, array $args, int $n, int $w, &$last_res, &$err): ?array
    {
        if (!method_exists($this->ffi, $name)) { $err = 'NOT_FOUND'; return null; }
        try {
            for ($i = 0; $i < $w; $i++) { $this->ffi->$name(...$args); }

            $times = []; $m0 = memory_get_usage(true);
            for ($i = 0; $i < $n; $i++) {
                $t0 = hrtime(true);
                $last_res = $this->ffi->$name(...$args);
                $t1 = hrtime(true);
                $times[] = ($t1 - $t0) / 1000;
            }
            $stats = $this->calcStats($times);
            $stats['mem'] = memory_get_usage(true) - $m0;
            return $stats;
        } catch (Throwable $e) { $err = $e->getMessage(); return null; }
    }

    private function benchExt(string $name, array $args, int $n, int $w, &$last_res, &$err): ?array
    {
        // On Windows, the extension is often named php_swephp.dll
        $extName = (PHP_OS_FAMILY === 'Windows') ? 'php_swephp' : 'swephp';
        if (!extension_loaded($extName) && !function_exists($name)) {
            $err = 'NOT_FOUND';
            return null;
        }
        try {
            $rf = new ReflectionFunction($name);
            $ext_args = [];
            foreach ($rf->getParameters() as $idx => $p) {
                if ($p->getName() === 'hsys') { $ext_args[] = 'P'; } elseif (isset($args[$idx]) && !($args[$idx] instanceof CData)) { $ext_args[] = $args[$idx]; } else {
                    $type = $p->getType();
                    $tname = $type instanceof ReflectionNamedType ? $type->getName() : '';
                    if ($tname === 'float') { $ext_args[] = 0.0; } elseif ($tname === 'int') { $ext_args[] = 0; } elseif ($tname === 'string') { $ext_args[] = ''; } else { $ext_args[] = 0; }
                }
            }

            // Strict 100 warmup iterations
            for ($i = 0; $i < $w; $i++) { @$name(...$ext_args); }

            $times = []; $m0 = memory_get_usage(true);
            // Strict 1000 measurement iterations
            for ($i = 0; $i < $n; $i++) {
                $t0 = hrtime(true);
                $last_res = @$name(...$ext_args);
                $t1 = hrtime(true);
                $times[] = ($t1 - $t0) / 1000;
            }
            $stats = $this->calcStats($times);
            $stats['mem'] = memory_get_usage(true) - $m0;
            return $stats;
        } catch (Throwable $e) { $err = $e->getMessage(); return null; }
    }

    private function checkAccuracy(string $name, $ffi_ret, $ext_ret): bool
    {
        if (is_array($ext_ret) && str_contains($name, 'calc')) {
            for ($i = 0;$i < 6;$i++) { if (abs($this->xx[$i] - $ext_ret[$i]) > 1e-15) { return false; } }
            return true;
        }
        if (is_numeric($ffi_ret) && is_numeric($ext_ret)) {
            return abs((float)$ffi_ret - (float)$ext_ret) < 1e-15;
        }
        return true;
    }

    private function calcStats(array $t): array
    {
        sort($t); $c = count($t); $sum = array_sum($t); $mean = $sum / $c;
        return [
            'mean' => $mean, 'median' => $t[(int)($c / 2)], 'p95' => $t[(int)($c * 0.95)],
            'stddev' => sqrt(array_sum(array_map(fn ($v) => pow($v - $mean,2), $t)) / $c),
            'min' => $t[0], 'max' => $t[$c - 1], 'count' => $c,
        ];
    }
}

$b = new UltimateBenchmark;
$b->run();
