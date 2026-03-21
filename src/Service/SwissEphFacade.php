<?php

declare(strict_types=1);

namespace SwissEph\Service;

use Illuminate\Support\Facades\Facade;
use SwissEph\FFI\SwissEphFFI;

/**
 * Laravel Facade for Swiss Ephemeris FFI
 * 
 * @method static array|false swe_calc_ut(float $tjd_ut, int $planet, int $flags = 0, ?string &$error = null)
 * @method static array|false swe_calc(float $tjd, int $planet, int $flags = 0, ?string &$error = null)
 * @method static array|false swe_houses(float $tjd_ut, float $geolat, float $geolon, string $hsys = 'P', ?string &$error = null)
 * @method static array|false swe_houses_armc(float $armc, float $geolat, float $eps, string $hsys = 'P', ?string &$error = null)
 * @method static float swe_julday(int $year, int $month, int $day, float $hour, int $gregflag = 1)
 * @method static array swe_revjul(float $tjd, int $gregflag = 1)
 * @method static float swe_deltat(float $tjd)
 * @method static float swe_get_ayanamsa(float $tjd)
 * @method static float swe_get_ayanamsa_ut(float $tjd_ut)
 * @method static void swe_set_epath(string $path)
 * @method static string swe_get_epath()
 * @method static void swe_set_jpl_file(string $fname)
 * @method static void swe_close()
 * @method static string swe_version()
 * @method static void swe_set_sid_mode(int $sid_mode)
 * @method static array|false swe_rise_trans(float $tjd_ut, int $ipl, string $starname = '', int $rsmi = 0, array $geopos = [0.0, 0.0, 0.0], float $atpress = 1013.25, float $attemp = 15.0, ?string &$error = null)
 * @method static array swe_azalt(float $tjd_ut, int $calc_flag, array $geopos, array $xin, float $atpress = 1013.25, float $attemp = 15.0)
 * @method static array|false swe_sol_eclipse_when_loc(float $tjd_start, int $ifl, array $geopos, ?string &$error = null)
 * @method static array|false swe_pheno(float $tjd_ut, int $ipl, int $iflag = 0, ?string &$error = null)
 * @method static array swe_split_deg(float $degrees, int $roundflag = 0)
 * @method static string swe_get_constellation(array $x)
 * @method static string swe_get_planet_name(int $ipl)
 * @method static array|false swe_nod_aps(float $tjd_ut, int $ipl, int $iflag = 0, int $icalc = 0, ?string &$error = null)
 * @method static float swe_get_ecliptic_obliquity(float $tjd)
 * @method static array swe_nutation(float $tjd, int $iflag = 0)
 * @method static float swe_time_equ(float $tjd)
 * @method static array swe_cotrans(float $x, float $y, float $z, float $eps)
 * @method static float swe_refrac(float $inalt, float $atpress = 1013.25, float $attemp = 15.0, int $calc_flag = 0)
 * 
 * @see \SwissEph\FFI\SwissEphFFI
 */
class SwissEphFacade extends Facade
{
    /**
     * Get the registered name of the component in the service container.
     * 
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'swisseph';
    }
}
