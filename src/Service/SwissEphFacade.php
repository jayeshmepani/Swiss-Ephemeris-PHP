<?php

declare(strict_types=1);

namespace SwissEph\Service;

use Illuminate\Support\Facades\Facade;
use SwissEph\FFI\SwissEphFFI;

/**
 * Laravel Facade for Swiss Ephemeris FFI.
 *
 * Provides static-like access to SwissEphFFI methods through Laravel's facade pattern.
 * All methods delegate directly to the underlying SwissEphFFI singleton instance.
 *
 * @author Jayesh Patel <jayeshmepani777@gmail.com>
 *
 * @method static array|false swe_calc_ut(float $tjd_ut, int $planet, int $flags = 0, ?string &$error = null) Calculate planet position (UT time)
 * @method static array|false swe_calc(float $tjd, int $planet, int $flags = 0, ?string &$error = null) Calculate planet position (ET time)
 * @method static array|false swe_houses(float $tjd_ut, float $geolat, float $geolon, string $hsys = 'P', ?string &$error = null) Calculate house cusps
 * @method static array|false swe_houses_armc(float $armc, float $geolat, float $eps, string $hsys = 'P', ?string &$error = null) Calculate houses from ARMC
 * @method static float swe_julday(int $year, int $month, int $day, float $hour, int $gregflag = 1) Convert date to Julian Day
 * @method static array swe_revjul(float $tjd, int $gregflag = 1) Convert Julian Day to date
 * @method static float swe_deltat(float $tjd) Get Delta T (difference between ET and UT)
 * @method static float swe_get_ayanamsa(float $tjd) Get ayanamsa value (ET)
 * @method static float swe_get_ayanamsa_ut(float $tjd_ut) Get ayanamsa value (UT)
 * @method static void swe_set_epath(string $path) Set ephemeris file path
 * @method static string swe_get_epath() Get ephemeris file path
 * @method static void swe_set_jpl_file(string $fname) Set JPL ephemeris file name
 * @method static void swe_close() Close ephemeris and free resources
 * @method static string swe_version() Get Swiss Ephemeris version string
 * @method static void swe_set_sid_mode(int $sid_mode) Set sidereal mode
 * @method static array|false swe_rise_trans(float $tjd_ut, int $ipl, string $starname = '', int $rsmi = 0, array $geopos = [0.0, 0.0, 0.0], float $atpress = 1013.25, float $attemp = 15.0, ?string &$error = null) Calculate rise/set/transit times
 * @method static array swe_azalt(float $tjd_ut, int $calc_flag, array $geopos, array $xin, float $atpress = 1013.25, float $attemp = 15.0) Convert azimuth/altitude
 * @method static array|false swe_sol_eclipse_when_loc(float $tjd_start, int $ifl, array $geopos, ?string &$error = null) Calculate solar eclipse for location
 * @method static array|false swe_pheno(float $tjd_ut, int $ipl, int $iflag = 0, ?string &$error = null) Get planetary phenomena
 * @method static array swe_split_deg(float $degrees, int $roundflag = 0) Split degrees into sign/degree/minute/second
 * @method static string swe_get_constellation(array $x) Get constellation for position
 * @method static string swe_get_planet_name(int $ipl) Get planet name
 * @method static array|false swe_nod_aps(float $tjd_ut, int $ipl, int $iflag = 0, int $icalc = 0, ?string &$error = null) Calculate nodes and apsides
 * @method static float swe_get_ecliptic_obliquity(float $tjd) Get ecliptic obliquity
 * @method static array swe_nutation(float $tjd, int $iflag = 0) Get nutation values
 * @method static float swe_time_equ(float $tjd) Get time equation
 * @method static array swe_cotrans(float $x, float $y, float $z, float $eps) Coordinate transformation
 * @method static float swe_refrac(float $inalt, float $atpress = 1013.25, float $attemp = 15.0, int $calc_flag = 0) Calculate atmospheric refraction
 *
 * @see \SwissEph\FFI\SwissEphFFI
 */
final class SwissEphFacade extends Facade
{
    /** Get registered name in service container. */
    protected static function getFacadeAccessor(): string
    {
        return 'swisseph';
    }
}
