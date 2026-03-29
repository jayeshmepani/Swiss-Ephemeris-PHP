<?php

declare(strict_types=1);

namespace SwissEph\FFI;

use FFI;
use FFI\CData;
use FFI\Exception;
use RuntimeException;

/**
 * SwissEphFFI - Complete FFI binding for Swiss Ephemeris C Library.
 *
 * Provides 106 PHP wrapper functions - a complete 1:1 mapping of all functions
 * from the official Swiss Ephemeris C library (swephexp.h).
 *
 * This class loads the Swiss Ephemeris shared library (libswe.so) via PHP FFI
 * and exposes all C functions for direct astronomical calculations.
 *
 * Key Features:
 * - Direct C library calls without process spawning
 * - No text parsing - all values are native PHP types
 * - Framework-agnostic (works with Laravel, Symfony, or plain PHP)
 * - Pre-compiled libswe.so included for Linux x64
 * - All 106 functions from official C library (100% coverage)
 *
 * @example
 * ```php
 * $sweph = new SwissEphFFI();
 * $jd = $sweph->swe_julday(2000, 1, 1, 12.0, SwissEphFFI::SE_GREG_CAL);
 * $xx = $sweph->getFFI()->new("double[6]");
 * $serr = $sweph->getFFI()->new("char[256]");
 * $result = $sweph->swe_calc_ut($jd, SwissEphFFI::SE_SUN, SwissEphFFI::SEFLG_SPEED, $xx, $serr);
 * ```
 *
 * @author Jayesh Patel <jayeshmepani777@gmail.com>
 *
 * @link https://github.com/jayeshmepani/Swiss-Ephemeris-PHP
 * @link https://www.astro.com/swisseph/ Swiss Ephemeris Official Site
 */
final class SwissEphFFI
{
    // ======== CONSTANTS ========
    public const SE_AUNIT_TO_KM = (149597870.700);
    public const SE_AUNIT_TO_LIGHTYEAR = (1.0 / 63241.07708427);
    public const SE_AUNIT_TO_PARSEC = (1.0 / 206264.8062471);
    public const SE_JUL_CAL = 0;
    public const SE_GREG_CAL = 1;
    public const SE_ECL_NUT = -1;
    public const SE_SUN = 0;
    public const SE_MOON = 1;
    public const SE_MERCURY = 2;
    public const SE_VENUS = 3;
    public const SE_MARS = 4;
    public const SE_JUPITER = 5;
    public const SE_SATURN = 6;
    public const SE_URANUS = 7;
    public const SE_NEPTUNE = 8;
    public const SE_PLUTO = 9;
    public const SE_MEAN_NODE = 10;
    public const SE_TRUE_NODE = 11;
    public const SE_MEAN_APOG = 12;
    public const SE_OSCU_APOG = 13;
    public const SE_EARTH = 14;
    public const SE_CHIRON = 15;
    public const SE_PHOLUS = 16;
    public const SE_CERES = 17;
    public const SE_PALLAS = 18;
    public const SE_JUNO = 19;
    public const SE_VESTA = 20;
    public const SE_INTP_APOG = 21;
    public const SE_INTP_PERG = 22;
    public const SE_NPLANETS = 23;
    public const SE_PLMOON_OFFSET = 9000;
    public const SE_AST_OFFSET = 10000;
    public const SE_VARUNA = (self::SE_AST_OFFSET + 20000);
    public const SE_FICT_OFFSET = 40;
    public const SE_FICT_OFFSET_1 = 39;
    public const SE_FICT_MAX = 999;
    public const SE_NFICT_ELEM = 15;
    public const SE_COMET_OFFSET = 1000;
    public const SE_NALL_NAT_POINTS = (self::SE_NPLANETS + self::SE_NFICT_ELEM);
    public const SE_CUPIDO = 40;
    public const SE_HADES = 41;
    public const SE_ZEUS = 42;
    public const SE_KRONOS = 43;
    public const SE_APOLLON = 44;
    public const SE_ADMETOS = 45;
    public const SE_VULKANUS = 46;
    public const SE_POSEIDON = 47;
    public const SE_ISIS = 48;
    public const SE_NIBIRU = 49;
    public const SE_HARRINGTON = 50;
    public const SE_NEPTUNE_LEVERRIER = 51;
    public const SE_NEPTUNE_ADAMS = 52;
    public const SE_PLUTO_LOWELL = 53;
    public const SE_PLUTO_PICKERING = 54;
    public const SE_VULCAN = 55;
    public const SE_WHITE_MOON = 56;
    public const SE_PROSERPINA = 57;
    public const SE_WALDEMATH = 58;
    public const SE_FIXSTAR = -10;
    public const SE_ASC = 0;
    public const SE_MC = 1;
    public const SE_ARMC = 2;
    public const SE_VERTEX = 3;
    public const SE_EQUASC = 4;
    public const SE_COASC1 = 5;
    public const SE_COASC2 = 6;
    public const SE_POLASC = 7;
    public const SE_NASCMC = 8;
    public const SEFLG_JPLEPH = 1;
    public const SEFLG_SWIEPH = 2;
    public const SEFLG_MOSEPH = 4;
    public const SEFLG_HELCTR = 8;
    public const SEFLG_TRUEPOS = 16;
    public const SEFLG_J2000 = 32;
    public const SEFLG_NONUT = 64;
    public const SEFLG_SPEED3 = 128;
    public const SEFLG_SPEED = 256;
    public const SEFLG_NOGDEFL = 512;
    public const SEFLG_NOABERR = 1024;
    public const SEFLG_ASTROMETRIC = (self::SEFLG_NOABERR | self::SEFLG_NOGDEFL);
    public const SEFLG_EQUATORIAL = (2 * 1024);
    public const SEFLG_XYZ = (4 * 1024);
    public const SEFLG_RADIANS = (8 * 1024);
    public const SEFLG_BARYCTR = (16 * 1024);
    public const SEFLG_TOPOCTR = (32 * 1024);
    public const SEFLG_ORBEL_AA = self::SEFLG_TOPOCTR;
    public const SEFLG_TROPICAL = (0);
    public const SEFLG_SIDEREAL = (64 * 1024);
    public const SEFLG_ICRS = (128 * 1024);
    public const SEFLG_DPSIDEPS_1980 = (256 * 1024);
    public const SEFLG_JPLHOR = self::SEFLG_DPSIDEPS_1980;
    public const SEFLG_JPLHOR_APPROX = (512 * 1024);
    public const SEFLG_CENTER_BODY = (1024 * 1024);
    public const SEFLG_TEST_PLMOON = (2 * 1024 * 1024 | self::SEFLG_J2000 | self::SEFLG_ICRS | self::SEFLG_HELCTR | self::SEFLG_TRUEPOS);
    public const SE_SIDBITS = 256;
    public const SE_SIDBIT_ECL_T0 = 256;
    public const SE_SIDBIT_SSY_PLANE = 512;
    public const SE_SIDBIT_USER_UT = 1024;
    public const SE_SIDBIT_ECL_DATE = 2048;
    public const SE_SIDBIT_NO_PREC_OFFSET = 4096;
    public const SE_SIDBIT_PREC_ORIG = 8192;
    public const SE_SIDM_FAGAN_BRADLEY = 0;
    public const SE_SIDM_LAHIRI = 1;
    public const SE_SIDM_DELUCE = 2;
    public const SE_SIDM_RAMAN = 3;
    public const SE_SIDM_USHASHASHI = 4;
    public const SE_SIDM_KRISHNAMURTI = 5;
    public const SE_SIDM_DJWHAL_KHUL = 6;
    public const SE_SIDM_YUKTESHWAR = 7;
    public const SE_SIDM_JN_BHASIN = 8;
    public const SE_SIDM_BABYL_KUGLER1 = 9;
    public const SE_SIDM_BABYL_KUGLER2 = 10;
    public const SE_SIDM_BABYL_KUGLER3 = 11;
    public const SE_SIDM_BABYL_HUBER = 12;
    public const SE_SIDM_BABYL_ETPSC = 13;
    public const SE_SIDM_ALDEBARAN_15TAU = 14;
    public const SE_SIDM_HIPPARCHOS = 15;
    public const SE_SIDM_SASSANIAN = 16;
    public const SE_SIDM_GALCENT_0SAG = 17;
    public const SE_SIDM_J2000 = 18;
    public const SE_SIDM_J1900 = 19;
    public const SE_SIDM_B1950 = 20;
    public const SE_SIDM_SURYASIDDHANTA = 21;
    public const SE_SIDM_SURYASIDDHANTA_MSUN = 22;
    public const SE_SIDM_ARYABHATA = 23;
    public const SE_SIDM_ARYABHATA_MSUN = 24;
    public const SE_SIDM_SS_REVATI = 25;
    public const SE_SIDM_SS_CITRA = 26;
    public const SE_SIDM_TRUE_CITRA = 27;
    public const SE_SIDM_TRUE_REVATI = 28;
    public const SE_SIDM_TRUE_PUSHYA = 29;
    public const SE_SIDM_GALCENT_RGILBRAND = 30;
    public const SE_SIDM_GALEQU_IAU1958 = 31;
    public const SE_SIDM_GALEQU_TRUE = 32;
    public const SE_SIDM_GALEQU_MULA = 33;
    public const SE_SIDM_GALALIGN_MARDYKS = 34;
    public const SE_SIDM_TRUE_MULA = 35;
    public const SE_SIDM_GALCENT_MULA_WILHELM = 36;
    public const SE_SIDM_ARYABHATA_522 = 37;
    public const SE_SIDM_BABYL_BRITTON = 38;
    public const SE_SIDM_TRUE_SHEORAN = 39;
    public const SE_SIDM_GALCENT_COCHRANE = 40;
    public const SE_SIDM_GALEQU_FIORENZA = 41;
    public const SE_SIDM_VALENS_MOON = 42;
    public const SE_SIDM_LAHIRI_1940 = 43;
    public const SE_SIDM_LAHIRI_VP285 = 44;
    public const SE_SIDM_KRISHNAMURTI_VP291 = 45;
    public const SE_SIDM_LAHIRI_ICRC = 46;
    public const SE_SIDM_USER = 255;
    public const SE_NSIDM_PREDEF = 47;
    public const SE_NODBIT_MEAN = 1;
    public const SE_NODBIT_OSCU = 2;
    public const SE_NODBIT_OSCU_BAR = 4;
    public const SE_NODBIT_FOPOINT = 256;
    public const SEFLG_DEFAULTEPH = self::SEFLG_SWIEPH;
    public const SE_MAX_STNAME = 256;
    public const SE_ECL_CENTRAL = 1;
    public const SE_ECL_NONCENTRAL = 2;
    public const SE_ECL_TOTAL = 4;
    public const SE_ECL_ANNULAR = 8;
    public const SE_ECL_PARTIAL = 16;
    public const SE_ECL_ANNULAR_TOTAL = 32;
    public const SE_ECL_HYBRID = 32;
    public const SE_ECL_PENUMBRAL = 64;
    public const SE_ECL_ALLTYPES_SOLAR = (self::SE_ECL_CENTRAL | self::SE_ECL_NONCENTRAL | self::SE_ECL_TOTAL | self::SE_ECL_ANNULAR | self::SE_ECL_PARTIAL | self::SE_ECL_ANNULAR_TOTAL);
    public const SE_ECL_ALLTYPES_LUNAR = (self::SE_ECL_TOTAL | self::SE_ECL_PARTIAL | self::SE_ECL_PENUMBRAL);
    public const SE_ECL_VISIBLE = 128;
    public const SE_ECL_MAX_VISIBLE = 256;
    public const SE_ECL_1ST_VISIBLE = 512;
    public const SE_ECL_PARTBEG_VISIBLE = 512;
    public const SE_ECL_2ND_VISIBLE = 1024;
    public const SE_ECL_TOTBEG_VISIBLE = 1024;
    public const SE_ECL_3RD_VISIBLE = 2048;
    public const SE_ECL_TOTEND_VISIBLE = 2048;
    public const SE_ECL_4TH_VISIBLE = 4096;
    public const SE_ECL_PARTEND_VISIBLE = 4096;
    public const SE_ECL_PENUMBBEG_VISIBLE = 8192;
    public const SE_ECL_PENUMBEND_VISIBLE = 16384;
    public const SE_ECL_OCC_BEG_DAYLIGHT = 8192;
    public const SE_ECL_OCC_END_DAYLIGHT = 16384;
    public const SE_ECL_ONE_TRY = (32 * 1024);
    public const SE_CALC_RISE = 1;
    public const SE_CALC_SET = 2;
    public const SE_CALC_MTRANSIT = 4;
    public const SE_CALC_ITRANSIT = 8;
    public const SE_BIT_DISC_CENTER = 256;
    public const SE_BIT_DISC_BOTTOM = 8192;
    public const SE_BIT_GEOCTR_NO_ECL_LAT = 128;
    public const SE_BIT_NO_REFRACTION = 512;
    public const SE_BIT_CIVIL_TWILIGHT = 1024;
    public const SE_BIT_NAUTIC_TWILIGHT = 2048;
    public const SE_BIT_ASTRO_TWILIGHT = 4096;
    public const SE_BIT_FIXED_DISC_SIZE = 16384;
    public const SE_BIT_FORCE_SLOW_METHOD = 32768;
    public const SE_BIT_HINDU_RISING = (self::SE_BIT_DISC_CENTER | self::SE_BIT_NO_REFRACTION | self::SE_BIT_GEOCTR_NO_ECL_LAT);
    public const SE_ECL2HOR = 0;
    public const SE_EQU2HOR = 1;
    public const SE_HOR2ECL = 0;
    public const SE_HOR2EQU = 1;
    public const SE_TRUE_TO_APP = 0;
    public const SE_APP_TO_TRUE = 1;
    public const SE_DE_NUMBER = 431;
    public const SE_FNAME_DE200 = 'de200.eph';
    public const SE_FNAME_DE403 = 'de403.eph';
    public const SE_FNAME_DE404 = 'de404.eph';
    public const SE_FNAME_DE405 = 'de405.eph';
    public const SE_FNAME_DE406 = 'de406.eph';
    public const SE_FNAME_DE431 = 'de431.eph';
    public const SE_FNAME_DFT = self::SE_FNAME_DE431;
    public const SE_FNAME_DFT2 = self::SE_FNAME_DE406;
    public const SE_STARFILE_OLD = 'fixstars.cat';
    public const SE_STARFILE = 'sefstars.txt';
    public const SE_ASTNAMFILE = 'seasnam.txt';
    public const SE_FICTFILE = 'seorbel.txt';
    public const SE_EPHE_PATH = '.:/users/ephe2/:/users/ephe/';
    public const SE_SPLIT_DEG_ROUND_SEC = 1;
    public const SE_SPLIT_DEG_ROUND_MIN = 2;
    public const SE_SPLIT_DEG_ROUND_DEG = 4;
    public const SE_SPLIT_DEG_ZODIACAL = 8;
    public const SE_SPLIT_DEG_NAKSHATRA = 1024;
    public const SE_SPLIT_DEG_KEEP_SIGN = 16;
    public const SE_SPLIT_DEG_KEEP_DEG = 32;
    public const SE_HELIACAL_RISING = 1;
    public const SE_HELIACAL_SETTING = 2;
    public const SE_MORNING_FIRST = self::SE_HELIACAL_RISING;
    public const SE_EVENING_LAST = self::SE_HELIACAL_SETTING;
    public const SE_EVENING_FIRST = 3;
    public const SE_MORNING_LAST = 4;
    public const SE_ACRONYCHAL_RISING = 5;
    public const SE_ACRONYCHAL_SETTING = 6;
    public const SE_COSMICAL_SETTING = self::SE_ACRONYCHAL_SETTING;
    public const SE_HELFLAG_LONG_SEARCH = 128;
    public const SE_HELFLAG_HIGH_PRECISION = 256;
    public const SE_HELFLAG_OPTICAL_PARAMS = 512;
    public const SE_HELFLAG_NO_DETAILS = 1024;
    public const SE_HELFLAG_SEARCH_1_PERIOD = (1 << 11);
    public const SE_HELFLAG_VISLIM_DARK = (1 << 12);
    public const SE_HELFLAG_VISLIM_NOMOON = (1 << 13);
    public const SE_HELFLAG_VISLIM_PHOTOPIC = (1 << 14);
    public const SE_HELFLAG_VISLIM_SCOTOPIC = (1 << 15);
    public const SE_HELFLAG_AV = (1 << 16);
    public const SE_HELFLAG_AVKIND_VR = (1 << 16);
    public const SE_HELFLAG_AVKIND_PTO = (1 << 17);
    public const SE_HELFLAG_AVKIND_MIN7 = (1 << 18);
    public const SE_HELFLAG_AVKIND_MIN9 = (1 << 19);
    public const SE_HELFLAG_AVKIND = (self::SE_HELFLAG_AVKIND_VR | self::SE_HELFLAG_AVKIND_PTO | self::SE_HELFLAG_AVKIND_MIN7 | self::SE_HELFLAG_AVKIND_MIN9);
    public const SE_HELIACAL_LONG_SEARCH = 128;
    public const SE_HELIACAL_HIGH_PRECISION = 256;
    public const SE_HELIACAL_OPTICAL_PARAMS = 512;
    public const SE_HELIACAL_NO_DETAILS = 1024;
    public const SE_HELIACAL_SEARCH_1_PERIOD = (1 << 11);
    public const SE_HELIACAL_VISLIM_DARK = (1 << 12);
    public const SE_HELIACAL_VISLIM_NOMOON = (1 << 13);
    public const SE_HELIACAL_VISLIM_PHOTOPIC = (1 << 14);
    public const SE_HELIACAL_AVKIND_VR = (1 << 15);
    public const SE_HELIACAL_AVKIND_PTO = (1 << 16);
    public const SE_HELIACAL_AVKIND_MIN7 = (1 << 17);
    public const SE_HELIACAL_AVKIND_MIN9 = (1 << 18);
    public const SE_HELIACAL_AVKIND = (self::SE_HELFLAG_AVKIND_VR | self::SE_HELFLAG_AVKIND_PTO | self::SE_HELFLAG_AVKIND_MIN7 | self::SE_HELFLAG_AVKIND_MIN9);
    public const SE_PHOTOPIC_FLAG = 0;
    public const SE_SCOTOPIC_FLAG = 1;
    public const SE_MIXEDOPIC_FLAG = 2;
    public const SE_TIDAL_DE200 = (-23.8946);
    public const SE_TIDAL_DE403 = (-25.580);
    public const SE_TIDAL_DE404 = (-25.580);
    public const SE_TIDAL_DE405 = (-25.826);
    public const SE_TIDAL_DE406 = (-25.826);
    public const SE_TIDAL_DE421 = (-25.85);
    public const SE_TIDAL_DE422 = (-25.85);
    public const SE_TIDAL_DE430 = (-25.82);
    public const SE_TIDAL_DE431 = (-25.80);
    public const SE_TIDAL_DE441 = (-25.936);
    public const SE_TIDAL_26 = (-26.0);
    public const SE_TIDAL_STEPHENSON_2016 = (-25.85);
    public const SE_TIDAL_DEFAULT = self::SE_TIDAL_DE431;
    public const SE_TIDAL_AUTOMATIC = 999999;
    public const SE_TIDAL_MOSEPH = self::SE_TIDAL_DE404;
    public const SE_TIDAL_SWIEPH = self::SE_TIDAL_DEFAULT;
    public const SE_TIDAL_JPLEPH = self::SE_TIDAL_DEFAULT;
    public const SE_DELTAT_AUTOMATIC = (-1E-10);
    public const SE_MODEL_DELTAT = 0;
    public const SE_MODEL_PREC_LONGTERM = 1;
    public const SE_MODEL_PREC_SHORTTERM = 2;
    public const SE_MODEL_NUT = 3;
    public const SE_MODEL_BIAS = 4;
    public const SE_MODEL_JPLHOR_MODE = 5;
    public const SE_MODEL_JPLHORA_MODE = 6;
    public const SE_MODEL_SIDT = 7;
    public const SEMOD_NPREC = 11;
    public const SEMOD_PREC_IAU_1976 = 1;
    public const SEMOD_PREC_LASKAR_1986 = 2;
    public const SEMOD_PREC_WILL_EPS_LASK = 3;
    public const SEMOD_PREC_WILLIAMS_1994 = 4;
    public const SEMOD_PREC_SIMON_1994 = 5;
    public const SEMOD_PREC_IAU_2000 = 6;
    public const SEMOD_PREC_BRETAGNON_2003 = 7;
    public const SEMOD_PREC_IAU_2006 = 8;
    public const SEMOD_PREC_VONDRAK_2011 = 9;
    public const SEMOD_PREC_OWEN_1990 = 10;
    public const SEMOD_PREC_NEWCOMB = 11;
    public const SEMOD_PREC_DEFAULT = self::SEMOD_PREC_VONDRAK_2011;
    public const SEMOD_PREC_DEFAULT_SHORT = self::SEMOD_PREC_VONDRAK_2011;
    public const SEMOD_NNUT = 5;
    public const SEMOD_NUT_IAU_1980 = 1;
    public const SEMOD_NUT_IAU_CORR_1987 = 2;
    public const SEMOD_NUT_IAU_2000A = 3;
    public const SEMOD_NUT_IAU_2000B = 4;
    public const SEMOD_NUT_WOOLARD = 5;
    public const SEMOD_NUT_DEFAULT = self::SEMOD_NUT_IAU_2000B;
    public const SEMOD_NSIDT = 4;
    public const SEMOD_SIDT_IAU_1976 = 1;
    public const SEMOD_SIDT_IAU_2006 = 2;
    public const SEMOD_SIDT_IERS_CONV_2010 = 3;
    public const SEMOD_SIDT_LONGTERM = 4;
    public const SEMOD_SIDT_DEFAULT = self::SEMOD_SIDT_LONGTERM;
    public const SEMOD_NBIAS = 3;
    public const SEMOD_BIAS_NONE = 1;
    public const SEMOD_BIAS_IAU2000 = 2;
    public const SEMOD_BIAS_IAU2006 = 3;
    public const SEMOD_BIAS_DEFAULT = self::SEMOD_BIAS_IAU2006;
    public const SEMOD_NJPLHOR = 2;
    public const SEMOD_JPLHOR_LONG_AGREEMENT = 1;
    public const SEMOD_JPLHOR_DEFAULT = self::SEMOD_JPLHOR_LONG_AGREEMENT;
    public const SEMOD_NJPLHORA = 3;
    public const SEMOD_JPLHORA_1 = 1;
    public const SEMOD_JPLHORA_2 = 2;
    public const SEMOD_JPLHORA_3 = 3;
    public const SEMOD_JPLHORA_DEFAULT = self::SEMOD_JPLHORA_3;
    public const SEMOD_NDELTAT = 5;
    public const SEMOD_DELTAT_STEPHENSON_MORRISON_1984 = 1;
    public const SEMOD_DELTAT_STEPHENSON_1997 = 2;
    public const SEMOD_DELTAT_STEPHENSON_MORRISON_2004 = 3;
    public const SEMOD_DELTAT_ESPENAK_MEEUS_2006 = 4;
    public const SEMOD_DELTAT_STEPHENSON_ETC_2016 = 5;
    public const SEMOD_DELTAT_DEFAULT = self::SEMOD_DELTAT_STEPHENSON_ETC_2016;
    public const SE_HOUSES_PLACIDUS = 'P';
    public const SE_HOUSES_KOCH = 'K';
    public const SE_HOUSES_PORPHYRIUS = 'O';
    public const SE_HOUSES_REGIOMONTANUS = 'R';
    public const SE_HOUSES_CAMPANO = 'C';
    public const SE_HOUSES_EQUAL = 'E';
    public const SE_HOUSES_EQUAL_VEHIC = 'V';
    public const SE_HOUSES_POLICH_PAGE = 'T';
    public const SE_HOUSES_ALCABITUS = 'B';
    public const SE_HOUSES_MORINUS = 'M';
    public const SE_HOUSES_KRUSINSKI = 'U';
    public const OK = 0;
    public const ERR = -1;
    /** FFI instance (singleton pattern) */
    private static ?FFI $ffi = null;

    /** Path to the loaded library */
    private static ?string $libraryPath = null;

    // ======== CONSTRUCTOR ========

    /**
     * Initialize Swiss Ephemeris FFI.
     *
     * Loads the Swiss Ephemeris shared library (libswe.so) and initializes FFI.
     * Uses singleton pattern - only one FFI instance is created per request.
     *
     * @param string|null $libraryPath Optional custom path to libswe.so.
     *                                 If null, searches common locations automatically.
     *
     * @throws RuntimeException If library file is not found or FFI fails to load
     *
     * @example
     * ```php
     * // Auto-detect library location
     * $sweph = new SwissEphFFI();
     *
     * // Specify custom library path
     * $sweph = new SwissEphFFI('/path/to/libswe.so');
     * ```
     */
    public function __construct(?string $libraryPath = null)
    {
        if (self::$ffi !== null) {
            return;
        }

        self::$libraryPath = $libraryPath ?? $this->findLibrary();

        if (!file_exists(self::$libraryPath)) {
            throw new RuntimeException('Swiss Ephemeris library not found at: ' . self::$libraryPath);
        }

        try {
            self::$ffi = FFI::cdef($this->getCDefinitions(), self::$libraryPath);
        } catch (Exception $e) {
            throw new RuntimeException('Failed to load library: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get singleton instance.
     *
     * @param string|null $libraryPath Optional custom library path
     */
    public static function getInstance(?string $libraryPath = null): self
    {
        return new self($libraryPath);
    }

    /**
     * Get FFI instance.
     *
     * Returns the underlying FFI object for creating C data structures
     * (arrays, pointers, structs) required by Swiss Ephemeris functions.
     *
     * @throws RuntimeException If FFI is not initialized
     *
     * @example
     * ```php
     * // Create C double array for planet positions
     * $xx = $sweph->getFFI()->new("double[6]");
     *
     * // Create C char array for error messages
     * $serr = $sweph->getFFI()->new("char[256]");
     * ```
     */
    public function getFFI(): FFI
    {
        if (self::$ffi === null) {
            throw new RuntimeException('FFI not initialized');
        }

        return self::$ffi;
    }

    // ======== METHODS ========
    public function swe_heliacal_ut(float $tjdstart_ut, mixed $geopos, mixed $datm, mixed $dobs, CData|string $ObjectName, int $TypeEvent, int $iflag, mixed $dret, CData|string $serr): int
    {
        return $this->getFFI()->swe_heliacal_ut($tjdstart_ut, $geopos, $datm, $dobs, $ObjectName, $TypeEvent, $iflag, $dret, $serr);
    }

    public function swe_heliacal_pheno_ut(float $tjd_ut, mixed $geopos, mixed $datm, mixed $dobs, CData|string $ObjectName, int $TypeEvent, int $helflag, mixed $darr, CData|string $serr): int
    {
        return $this->getFFI()->swe_heliacal_pheno_ut($tjd_ut, $geopos, $datm, $dobs, $ObjectName, $TypeEvent, $helflag, $darr, $serr);
    }

    public function swe_vis_limit_mag(float $tjdut, mixed $geopos, mixed $datm, mixed $dobs, CData|string $ObjectName, int $helflag, mixed $dret, CData|string $serr): int
    {
        return $this->getFFI()->swe_vis_limit_mag($tjdut, $geopos, $datm, $dobs, $ObjectName, $helflag, $dret, $serr);
    }

    public function swe_heliacal_angle(float $tjdut, mixed $dgeo, mixed $datm, mixed $dobs, int $helflag, float $mag, float $azi_obj, float $azi_sun, float $azi_moon, float $alt_moon, mixed $dret, CData|string $serr): int
    {
        return $this->getFFI()->swe_heliacal_angle($tjdut, $dgeo, $datm, $dobs, $helflag, $mag, $azi_obj, $azi_sun, $azi_moon, $alt_moon, $dret, $serr);
    }

    public function swe_topo_arcus_visionis(float $tjdut, mixed $dgeo, mixed $datm, mixed $dobs, int $helflag, float $mag, float $azi_obj, float $alt_obj, float $azi_sun, float $azi_moon, float $alt_moon, mixed $dret, CData|string $serr): int
    {
        return $this->getFFI()->swe_topo_arcus_visionis($tjdut, $dgeo, $datm, $dobs, $helflag, $mag, $azi_obj, $alt_obj, $azi_sun, $azi_moon, $alt_moon, $dret, $serr);
    }

    public function swe_set_astro_models(CData|string $samod, int $iflag): void
    {
        $this->getFFI()->swe_set_astro_models($samod, $iflag);
    }

    public function swe_get_astro_models(CData|string $samod, CData|string $sdet, int $iflag): void
    {
        $this->getFFI()->swe_get_astro_models($samod, $sdet, $iflag);
    }

    public function swe_version(CData|string $p): ?string
    {
        $ptr = $this->getFFI()->swe_version($p);
        return $ptr !== null ? FFI::string($ptr) : null;
    }

    public function swe_get_library_path(CData|string $p): ?string
    {
        $ptr = $this->getFFI()->swe_get_library_path($p);
        return $ptr !== null ? FFI::string($ptr) : null;
    }

    public function swe_calc(float $tjd, int $ipl, int $iflag, mixed $xx, CData|string $serr): int
    {
        return $this->getFFI()->swe_calc($tjd, $ipl, $iflag, $xx, $serr);
    }

    public function swe_calc_ut(float $tjd_ut, int $ipl, int $iflag, mixed $xx, CData|string $serr): int
    {
        return $this->getFFI()->swe_calc_ut($tjd_ut, $ipl, $iflag, $xx, $serr);
    }

    public function swe_calc_pctr(float $tjd, int $ipl, int $iplctr, int $iflag, mixed $xxret, CData|string $serr): int
    {
        return $this->getFFI()->swe_calc_pctr($tjd, $ipl, $iplctr, $iflag, $xxret, $serr);
    }

    public function swe_solcross(float $x2cross, float $jd_et, int $flag, CData|string $serr): float
    {
        return $this->getFFI()->swe_solcross($x2cross, $jd_et, $flag, $serr);
    }

    public function swe_solcross_ut(float $x2cross, float $jd_ut, int $flag, CData|string $serr): float
    {
        return $this->getFFI()->swe_solcross_ut($x2cross, $jd_ut, $flag, $serr);
    }

    public function swe_mooncross(float $x2cross, float $jd_et, int $flag, CData|string $serr): float
    {
        return $this->getFFI()->swe_mooncross($x2cross, $jd_et, $flag, $serr);
    }

    public function swe_mooncross_ut(float $x2cross, float $jd_ut, int $flag, CData|string $serr): float
    {
        return $this->getFFI()->swe_mooncross_ut($x2cross, $jd_ut, $flag, $serr);
    }

    public function swe_mooncross_node(float $jd_et, int $flag, mixed $xlon, mixed $xlat, CData|string $serr): float
    {
        return $this->getFFI()->swe_mooncross_node($jd_et, $flag, $xlon, $xlat, $serr);
    }

    public function swe_mooncross_node_ut(float $jd_ut, int $flag, mixed $xlon, mixed $xlat, CData|string $serr): float
    {
        return $this->getFFI()->swe_mooncross_node_ut($jd_ut, $flag, $xlon, $xlat, $serr);
    }

    public function swe_helio_cross(int $ipl, float $x2cross, float $jd_et, int $iflag, int $dir, mixed $jd_cross, CData|string $serr): int
    {
        return $this->getFFI()->swe_helio_cross($ipl, $x2cross, $jd_et, $iflag, $dir, $jd_cross, $serr);
    }

    public function swe_helio_cross_ut(int $ipl, float $x2cross, float $jd_ut, int $iflag, int $dir, mixed $jd_cross, CData|string $serr): int
    {
        return $this->getFFI()->swe_helio_cross_ut($ipl, $x2cross, $jd_ut, $iflag, $dir, $jd_cross, $serr);
    }

    public function swe_fixstar(CData|string $star, float $tjd, int $iflag, mixed $xx, CData|string $serr): int
    {
        return $this->getFFI()->swe_fixstar($star, $tjd, $iflag, $xx, $serr);
    }

    public function swe_fixstar_ut(CData|string $star, float $tjd_ut, int $iflag, mixed $xx, CData|string $serr): int
    {
        return $this->getFFI()->swe_fixstar_ut($star, $tjd_ut, $iflag, $xx, $serr);
    }

    public function swe_fixstar_mag(CData|string $star, mixed $mag, CData|string $serr): int
    {
        return $this->getFFI()->swe_fixstar_mag($star, $mag, $serr);
    }

    public function swe_fixstar2(CData|string $star, float $tjd, int $iflag, mixed $xx, CData|string $serr): int
    {
        return $this->getFFI()->swe_fixstar2($star, $tjd, $iflag, $xx, $serr);
    }

    public function swe_fixstar2_ut(CData|string $star, float $tjd_ut, int $iflag, mixed $xx, CData|string $serr): int
    {
        return $this->getFFI()->swe_fixstar2_ut($star, $tjd_ut, $iflag, $xx, $serr);
    }

    public function swe_fixstar2_mag(CData|string $star, mixed $mag, CData|string $serr): int
    {
        return $this->getFFI()->swe_fixstar2_mag($star, $mag, $serr);
    }

    public function swe_close(): void
    {
        $this->getFFI()->swe_close();
    }

    public function swe_set_ephe_path(CData|string $path): void
    {
        $this->getFFI()->swe_set_ephe_path($path);
    }

    public function swe_set_jpl_file(CData|string $fname): void
    {
        $this->getFFI()->swe_set_jpl_file($fname);
    }

    public function swe_get_planet_name(int $ipl, CData|string $spname): ?string
    {
        $ptr = $this->getFFI()->swe_get_planet_name($ipl, $spname);
        return $ptr !== null ? FFI::string($ptr) : null;
    }

    public function swe_set_topo(float $geolon, float $geolat, float $geoalt): void
    {
        $this->getFFI()->swe_set_topo($geolon, $geolat, $geoalt);
    }

    public function swe_set_sid_mode(int $sid_mode, float $t0, float $ayan_t0): void
    {
        $this->getFFI()->swe_set_sid_mode($sid_mode, $t0, $ayan_t0);
    }

    public function swe_get_ayanamsa_ex(float $tjd_et, int $iflag, mixed $daya, CData|string $serr): int
    {
        return $this->getFFI()->swe_get_ayanamsa_ex($tjd_et, $iflag, $daya, $serr);
    }

    public function swe_get_ayanamsa_ex_ut(float $tjd_ut, int $iflag, mixed $daya, CData|string $serr): int
    {
        return $this->getFFI()->swe_get_ayanamsa_ex_ut($tjd_ut, $iflag, $daya, $serr);
    }

    public function swe_get_ayanamsa(float $tjd_et): float
    {
        return $this->getFFI()->swe_get_ayanamsa($tjd_et);
    }

    public function swe_get_ayanamsa_ut(float $tjd_ut): float
    {
        return $this->getFFI()->swe_get_ayanamsa_ut($tjd_ut);
    }

    public function swe_get_ayanamsa_name(int $isidmode): ?string
    {
        $ptr = $this->getFFI()->swe_get_ayanamsa_name($isidmode);
        return $ptr !== null ? FFI::string($ptr) : null;
    }

    public function swe_get_current_file_data(int $ifno, mixed $tfstart, mixed $tfend, mixed $denum): ?string
    {
        $ptr = $this->getFFI()->swe_get_current_file_data($ifno, $tfstart, $tfend, $denum);
        return $ptr !== null ? FFI::string($ptr) : null;
    }

    public function swe_date_conversion(int $y, int $m, int $d, float $utime, mixed $c, mixed $tjd): int
    {
        return $this->getFFI()->swe_date_conversion($y, $m, $d, $utime, $c, $tjd);
    }

    public function swe_julday(int $year, int $month, int $day, float $hour, int $gregflag): float
    {
        return $this->getFFI()->swe_julday($year, $month, $day, $hour, $gregflag);
    }

    public function swe_revjul(float $jd, int $gregflag, mixed $jyear, mixed $jmon, mixed $jday, mixed $jut): void
    {
        $this->getFFI()->swe_revjul($jd, $gregflag, $jyear, $jmon, $jday, $jut);
    }

    public function swe_utc_to_jd(int $iyear, int $imonth, int $iday, int $ihour, int $imin, float $dsec, int $gregflag, mixed $dret, CData|string $serr): int
    {
        return $this->getFFI()->swe_utc_to_jd($iyear, $imonth, $iday, $ihour, $imin, $dsec, $gregflag, $dret, $serr);
    }

    public function swe_jdet_to_utc(float $tjd_et, int $gregflag, mixed $iyear, mixed $imonth, mixed $iday, mixed $ihour, mixed $imin, mixed $dsec): void
    {
        $this->getFFI()->swe_jdet_to_utc($tjd_et, $gregflag, $iyear, $imonth, $iday, $ihour, $imin, $dsec);
    }

    public function swe_jdut1_to_utc(float $tjd_ut, int $gregflag, mixed $iyear, mixed $imonth, mixed $iday, mixed $ihour, mixed $imin, mixed $dsec): void
    {
        $this->getFFI()->swe_jdut1_to_utc($tjd_ut, $gregflag, $iyear, $imonth, $iday, $ihour, $imin, $dsec);
    }

    public function swe_utc_time_zone(int $iyear, int $imonth, int $iday, int $ihour, int $imin, float $dsec, float $d_timezone, mixed $iyear_out, mixed $imonth_out, mixed $iday_out, mixed $ihour_out, mixed $imin_out, mixed $dsec_out): void
    {
        $this->getFFI()->swe_utc_time_zone($iyear, $imonth, $iday, $ihour, $imin, $dsec, $d_timezone, $iyear_out, $imonth_out, $iday_out, $ihour_out, $imin_out, $dsec_out);
    }

    public function swe_houses(float $tjd_ut, float $geolat, float $geolon, int $hsys, mixed $cusps, mixed $ascmc): int
    {
        return $this->getFFI()->swe_houses($tjd_ut, $geolat, $geolon, $hsys, $cusps, $ascmc);
    }

    public function swe_houses_ex(float $tjd_ut, int $iflag, float $geolat, float $geolon, int $hsys, mixed $cusps, mixed $ascmc): int
    {
        return $this->getFFI()->swe_houses_ex($tjd_ut, $iflag, $geolat, $geolon, $hsys, $cusps, $ascmc);
    }

    public function swe_houses_ex2(float $tjd_ut, int $iflag, float $geolat, float $geolon, int $hsys, mixed $cusps, mixed $ascmc, mixed $cusp_speed, mixed $ascmc_speed, CData|string $serr): int
    {
        return $this->getFFI()->swe_houses_ex2($tjd_ut, $iflag, $geolat, $geolon, $hsys, $cusps, $ascmc, $cusp_speed, $ascmc_speed, $serr);
    }

    public function swe_houses_armc(float $armc, float $geolat, float $eps, int $hsys, mixed $cusps, mixed $ascmc): int
    {
        return $this->getFFI()->swe_houses_armc($armc, $geolat, $eps, $hsys, $cusps, $ascmc);
    }

    public function swe_houses_armc_ex2(float $armc, float $geolat, float $eps, int $hsys, mixed $cusps, mixed $ascmc, mixed $cusp_speed, mixed $ascmc_speed, CData|string $serr): int
    {
        return $this->getFFI()->swe_houses_armc_ex2($armc, $geolat, $eps, $hsys, $cusps, $ascmc, $cusp_speed, $ascmc_speed, $serr);
    }

    public function swe_house_pos(float $armc, float $geolat, float $eps, int $hsys, mixed $xpin, CData|string $serr): float
    {
        return $this->getFFI()->swe_house_pos($armc, $geolat, $eps, $hsys, $xpin, $serr);
    }

    public function swe_house_name(int $hsys): ?string
    {
        $ptr = $this->getFFI()->swe_house_name($hsys);
        return $ptr !== null ? FFI::string($ptr) : null;
    }

    public function swe_gauquelin_sector(float $t_ut, int $ipl, CData|string $starname, int $iflag, int $imeth, mixed $geopos, float $atpress, float $attemp, mixed $dgsect, CData|string $serr): int
    {
        return $this->getFFI()->swe_gauquelin_sector($t_ut, $ipl, $starname, $iflag, $imeth, $geopos, $atpress, $attemp, $dgsect, $serr);
    }

    public function swe_sol_eclipse_where(float $tjd, int $ifl, mixed $geopos, mixed $attr, CData|string $serr): int
    {
        return $this->getFFI()->swe_sol_eclipse_where($tjd, $ifl, $geopos, $attr, $serr);
    }

    public function swe_lun_occult_where(float $tjd, int $ipl, CData|string $starname, int $ifl, mixed $geopos, mixed $attr, CData|string $serr): int
    {
        return $this->getFFI()->swe_lun_occult_where($tjd, $ipl, $starname, $ifl, $geopos, $attr, $serr);
    }

    public function swe_sol_eclipse_how(float $tjd, int $ifl, mixed $geopos, mixed $attr, CData|string $serr): int
    {
        return $this->getFFI()->swe_sol_eclipse_how($tjd, $ifl, $geopos, $attr, $serr);
    }

    public function swe_sol_eclipse_when_loc(float $tjd_start, int $ifl, mixed $geopos, mixed $tret, mixed $attr, int $backward, CData|string $serr): int
    {
        return $this->getFFI()->swe_sol_eclipse_when_loc($tjd_start, $ifl, $geopos, $tret, $attr, $backward, $serr);
    }

    public function swe_lun_occult_when_loc(float $tjd_start, int $ipl, CData|string $starname, int $ifl, mixed $geopos, mixed $tret, mixed $attr, int $backward, CData|string $serr): int
    {
        return $this->getFFI()->swe_lun_occult_when_loc($tjd_start, $ipl, $starname, $ifl, $geopos, $tret, $attr, $backward, $serr);
    }

    public function swe_sol_eclipse_when_glob(float $tjd_start, int $ifl, int $ifltype, mixed $tret, int $backward, CData|string $serr): int
    {
        return $this->getFFI()->swe_sol_eclipse_when_glob($tjd_start, $ifl, $ifltype, $tret, $backward, $serr);
    }

    public function swe_lun_occult_when_glob(float $tjd_start, int $ipl, CData|string $starname, int $ifl, int $ifltype, mixed $tret, int $backward, CData|string $serr): int
    {
        return $this->getFFI()->swe_lun_occult_when_glob($tjd_start, $ipl, $starname, $ifl, $ifltype, $tret, $backward, $serr);
    }

    public function swe_lun_eclipse_how(float $tjd_ut, int $ifl, mixed $geopos, mixed $attr, CData|string $serr): int
    {
        return $this->getFFI()->swe_lun_eclipse_how($tjd_ut, $ifl, $geopos, $attr, $serr);
    }

    public function swe_lun_eclipse_when(float $tjd_start, int $ifl, int $ifltype, mixed $tret, int $backward, CData|string $serr): int
    {
        return $this->getFFI()->swe_lun_eclipse_when($tjd_start, $ifl, $ifltype, $tret, $backward, $serr);
    }

    public function swe_lun_eclipse_when_loc(float $tjd_start, int $ifl, mixed $geopos, mixed $tret, mixed $attr, int $backward, CData|string $serr): int
    {
        return $this->getFFI()->swe_lun_eclipse_when_loc($tjd_start, $ifl, $geopos, $tret, $attr, $backward, $serr);
    }

    public function swe_pheno(float $tjd, int $ipl, int $iflag, mixed $attr, CData|string $serr): int
    {
        return $this->getFFI()->swe_pheno($tjd, $ipl, $iflag, $attr, $serr);
    }

    public function swe_pheno_ut(float $tjd_ut, int $ipl, int $iflag, mixed $attr, CData|string $serr): int
    {
        return $this->getFFI()->swe_pheno_ut($tjd_ut, $ipl, $iflag, $attr, $serr);
    }

    public function swe_refrac(float $inalt, float $atpress, float $attemp, int $calc_flag): float
    {
        return $this->getFFI()->swe_refrac($inalt, $atpress, $attemp, $calc_flag);
    }

    public function swe_refrac_extended(float $inalt, float $geoalt, float $atpress, float $attemp, float $lapse_rate, int $calc_flag, mixed $dret): float
    {
        return $this->getFFI()->swe_refrac_extended($inalt, $geoalt, $atpress, $attemp, $lapse_rate, $calc_flag, $dret);
    }

    public function swe_set_lapse_rate(float $lapse_rate): void
    {
        $this->getFFI()->swe_set_lapse_rate($lapse_rate);
    }

    public function swe_azalt(float $tjd_ut, int $calc_flag, mixed $geopos, float $atpress, float $attemp, mixed $xin, mixed $xaz): void
    {
        $this->getFFI()->swe_azalt($tjd_ut, $calc_flag, $geopos, $atpress, $attemp, $xin, $xaz);
    }

    public function swe_azalt_rev(float $tjd_ut, int $calc_flag, mixed $geopos, mixed $xin, mixed $xout): void
    {
        $this->getFFI()->swe_azalt_rev($tjd_ut, $calc_flag, $geopos, $xin, $xout);
    }

    public function swe_rise_trans_true_hor(float $tjd_ut, int $ipl, CData|string $starname, int $epheflag, int $rsmi, mixed $geopos, float $atpress, float $attemp, float $horhgt, mixed $tret, CData|string $serr): int
    {
        return $this->getFFI()->swe_rise_trans_true_hor($tjd_ut, $ipl, $starname, $epheflag, $rsmi, $geopos, $atpress, $attemp, $horhgt, $tret, $serr);
    }

    public function swe_rise_trans(float $tjd_ut, int $ipl, CData|string $starname, int $epheflag, int $rsmi, mixed $geopos, float $atpress, float $attemp, mixed $tret, CData|string $serr): int
    {
        return $this->getFFI()->swe_rise_trans($tjd_ut, $ipl, $starname, $epheflag, $rsmi, $geopos, $atpress, $attemp, $tret, $serr);
    }

    public function swe_nod_aps(float $tjd_et, int $ipl, int $iflag, int $method, mixed $xnasc, mixed $xndsc, mixed $xperi, mixed $xaphe, CData|string $serr): int
    {
        return $this->getFFI()->swe_nod_aps($tjd_et, $ipl, $iflag, $method, $xnasc, $xndsc, $xperi, $xaphe, $serr);
    }

    public function swe_nod_aps_ut(float $tjd_ut, int $ipl, int $iflag, int $method, mixed $xnasc, mixed $xndsc, mixed $xperi, mixed $xaphe, CData|string $serr): int
    {
        return $this->getFFI()->swe_nod_aps_ut($tjd_ut, $ipl, $iflag, $method, $xnasc, $xndsc, $xperi, $xaphe, $serr);
    }

    public function swe_get_orbital_elements(float $tjd_et, int $ipl, int $iflag, mixed $dret, CData|string $serr): int
    {
        return $this->getFFI()->swe_get_orbital_elements($tjd_et, $ipl, $iflag, $dret, $serr);
    }

    public function swe_orbit_max_min_true_distance(float $tjd_et, int $ipl, int $iflag, mixed $dmax, mixed $dmin, mixed $dtrue, CData|string $serr): int
    {
        return $this->getFFI()->swe_orbit_max_min_true_distance($tjd_et, $ipl, $iflag, $dmax, $dmin, $dtrue, $serr);
    }

    public function swe_deltat(float $tjd): float
    {
        return $this->getFFI()->swe_deltat($tjd);
    }

    public function swe_deltat_ex(float $tjd, int $iflag, CData|string $serr): float
    {
        return $this->getFFI()->swe_deltat_ex($tjd, $iflag, $serr);
    }

    public function swe_time_equ(float $tjd, mixed $te, CData|string $serr): int
    {
        return $this->getFFI()->swe_time_equ($tjd, $te, $serr);
    }

    public function swe_lmt_to_lat(float $tjd_lmt, float $geolon, mixed $tjd_lat, CData|string $serr): int
    {
        return $this->getFFI()->swe_lmt_to_lat($tjd_lmt, $geolon, $tjd_lat, $serr);
    }

    public function swe_lat_to_lmt(float $tjd_lat, float $geolon, mixed $tjd_lmt, CData|string $serr): int
    {
        return $this->getFFI()->swe_lat_to_lmt($tjd_lat, $geolon, $tjd_lmt, $serr);
    }

    public function swe_sidtime0(float $tjd_ut, float $eps, float $nut): float
    {
        return $this->getFFI()->swe_sidtime0($tjd_ut, $eps, $nut);
    }

    public function swe_sidtime(float $tjd_ut): float
    {
        return $this->getFFI()->swe_sidtime($tjd_ut);
    }

    public function swe_set_interpolate_nut(int $do_interpolate): void
    {
        $this->getFFI()->swe_set_interpolate_nut($do_interpolate);
    }

    public function swe_cotrans(mixed $xpo, mixed $xpn, float $eps): void
    {
        $this->getFFI()->swe_cotrans($xpo, $xpn, $eps);
    }

    public function swe_cotrans_sp(mixed $xpo, mixed $xpn, float $eps): void
    {
        $this->getFFI()->swe_cotrans_sp($xpo, $xpn, $eps);
    }

    public function swe_get_tid_acc(): float
    {
        return $this->getFFI()->swe_get_tid_acc();
    }

    public function swe_set_tid_acc(float $t_acc): void
    {
        $this->getFFI()->swe_set_tid_acc($t_acc);
    }

    public function swe_set_delta_t_userdef(float $dt): void
    {
        $this->getFFI()->swe_set_delta_t_userdef($dt);
    }

    public function swe_degnorm(float $x): float
    {
        return $this->getFFI()->swe_degnorm($x);
    }

    public function swe_radnorm(float $x): float
    {
        return $this->getFFI()->swe_radnorm($x);
    }

    public function swe_rad_midp(float $x1, float $x0): float
    {
        return $this->getFFI()->swe_rad_midp($x1, $x0);
    }

    public function swe_deg_midp(float $x1, float $x0): float
    {
        return $this->getFFI()->swe_deg_midp($x1, $x0);
    }

    public function swe_split_deg(float $ddeg, int $roundflag, mixed $ideg, mixed $imin, mixed $isec, mixed $dsecfr, mixed $isgn): void
    {
        $this->getFFI()->swe_split_deg($ddeg, $roundflag, $ideg, $imin, $isec, $dsecfr, $isgn);
    }

    public function swe_csnorm(int $p): int
    {
        return $this->getFFI()->swe_csnorm($p);
    }

    public function swe_difcsn(int $p1, int $p2): int
    {
        return $this->getFFI()->swe_difcsn($p1, $p2);
    }

    public function swe_difdegn(float $p1, float $p2): float
    {
        return $this->getFFI()->swe_difdegn($p1, $p2);
    }

    public function swe_difcs2n(int $p1, int $p2): int
    {
        return $this->getFFI()->swe_difcs2n($p1, $p2);
    }

    public function swe_difdeg2n(float $p1, float $p2): float
    {
        return $this->getFFI()->swe_difdeg2n($p1, $p2);
    }

    public function swe_difrad2n(float $p1, float $p2): float
    {
        return $this->getFFI()->swe_difrad2n($p1, $p2);
    }

    public function swe_csroundsec(int $x): int
    {
        return $this->getFFI()->swe_csroundsec($x);
    }

    public function swe_d2l(float $x): int
    {
        return $this->getFFI()->swe_d2l($x);
    }

    public function swe_day_of_week(float $jd): int
    {
        return $this->getFFI()->swe_day_of_week($jd);
    }

    public function swe_cs2timestr(int $t, int $sep, int $suppressZero, CData|string $a): ?string
    {
        $ptr = $this->getFFI()->swe_cs2timestr($t, $sep, $suppressZero, $a);
        return $ptr !== null ? FFI::string($ptr) : null;
    }

    public function swe_cs2lonlatstr(int $t, mixed $pchar, mixed $mchar, CData|string $s): ?string
    {
        $ptr = $this->getFFI()->swe_cs2lonlatstr($t, $pchar, $mchar, $s);
        return $ptr !== null ? FFI::string($ptr) : null;
    }

    public function swe_cs2degstr(int $t, CData|string $a): ?string
    {
        $ptr = $this->getFFI()->swe_cs2degstr($t, $a);
        return $ptr !== null ? FFI::string($ptr) : null;
    }

    /**
     * Find Swiss Ephemeris library.
     *
     * Search order:
     * 1. Explicit env override (SWISSEPH_LIBRARY_PATH)
     * 2. Package's pre-compiled library (libs/<os-arch>/)
     * 3. Build directory (build/)
     * 4. System libraries
     *
     * @throws RuntimeException If library not found in any location
     *
     * @return string Path to the shared library
     */
    private function findLibrary(): string
    {
        $envPath = getenv('SWISSEPH_LIBRARY_PATH');
        if (is_string($envPath) && $envPath !== '') {
            return $envPath;
        }

        $platform = $this->detectPlatform();
        $packagePath = __DIR__ . '/../../libs/' . $platform['dir'] . '/' . $platform['file'];
        $buildPath = __DIR__ . '/../../build/' . $platform['file'];

        $candidates = [
            $packagePath,
            $buildPath,
        ];

        if ($platform['family'] === 'Linux') {
            $candidates[] = '/usr/local/lib/' . $platform['file'];
            $candidates[] = '/usr/lib/' . $platform['file'];
        }

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        // Default to expected package path for clearer error messages.
        return $packagePath;
    }

    /**
     * Detect OS family and CPU architecture for library lookup.
     *
     * @return array{family:string, dir:string, file:string}
     */
    private function detectPlatform(): array
    {
        $family = PHP_OS_FAMILY;
        $arch = strtolower(php_uname('m'));

        $arch = match (true) {
            in_array($arch, ['x86_64', 'amd64'], true) => 'x64',
            in_array($arch, ['aarch64', 'arm64'], true) => 'arm64',
            default => $arch,
        };

        return match ($family) {
            'Windows' => ['family' => $family, 'dir' => 'windows-' . $arch, 'file' => 'swe.dll'],
            'Darwin' => ['family' => $family, 'dir' => 'macos-' . $arch, 'file' => 'libswe.dylib'],
            default => ['family' => $family, 'dir' => 'linux-' . $arch, 'file' => 'libswe.so'],
        };
    }

    // ======== C DEFINITIONS ========
    private function getCDefinitions(): string
    {
        return <<<'CDEF'
        typedef double dbl;
        typedef int int32;
        
        #define PASCAL_CONV
        #define EXP16

        int32 swe_heliacal_ut(double tjdstart_ut, double *geopos, double *datm, double *dobs, char *ObjectName, int32 TypeEvent, int32 iflag, double *dret, char *serr);
        int32 swe_heliacal_pheno_ut(double tjd_ut, double *geopos, double *datm, double *dobs, char *ObjectName, int32 TypeEvent, int32 helflag, double *darr, char *serr);
        int32 swe_vis_limit_mag(double tjdut, double *geopos, double *datm, double *dobs, char *ObjectName, int32 helflag, double *dret, char *serr);
        int32 swe_heliacal_angle(double tjdut, double *dgeo, double *datm, double *dobs, int32 helflag, double mag, double azi_obj, double azi_sun, double azi_moon, double alt_moon, double *dret, char *serr);
        int32 swe_topo_arcus_visionis(double tjdut, double *dgeo, double *datm, double *dobs, int32 helflag, double mag, double azi_obj, double alt_obj, double azi_sun, double azi_moon, double alt_moon, double *dret, char *serr);
        void swe_set_astro_models(char *samod, int32 iflag);
        void swe_get_astro_models(char *samod, char *sdet, int32 iflag);
        char * swe_version(char *p);
        char * swe_get_library_path(char *p);
        int32 swe_calc(double tjd, int ipl, int32 iflag, double *xx, char *serr);
        int32 swe_calc_ut(double tjd_ut, int32 ipl, int32 iflag, double *xx, char *serr);
        int32 swe_calc_pctr(double tjd, int32 ipl, int32 iplctr, int32 iflag, double *xxret, char *serr);
        double swe_solcross(double x2cross, double jd_et, int32 flag, char *serr);
        double swe_solcross_ut(double x2cross, double jd_ut, int32 flag, char *serr);
        double swe_mooncross(double x2cross, double jd_et, int32 flag, char *serr);
        double swe_mooncross_ut(double x2cross, double jd_ut, int32 flag, char *serr);
        double swe_mooncross_node(double jd_et, int32 flag, double *xlon, double *xlat, char *serr);
        double swe_mooncross_node_ut(double jd_ut, int32 flag, double *xlon, double *xlat, char *serr);
        int32 swe_helio_cross(int32 ipl, double x2cross, double jd_et, int32 iflag, int32 dir, double *jd_cross, char *serr);
        int32 swe_helio_cross_ut(int32 ipl, double x2cross, double jd_ut, int32 iflag, int32 dir, double *jd_cross, char *serr);
        int32 swe_fixstar(char *star, double tjd, int32 iflag, double *xx, char *serr);
        int32 swe_fixstar_ut(char *star, double tjd_ut, int32 iflag, double *xx, char *serr);
        int32 swe_fixstar_mag(char *star, double *mag, char *serr);
        int32 swe_fixstar2(char *star, double tjd, int32 iflag, double *xx, char *serr);
        int32 swe_fixstar2_ut(char *star, double tjd_ut, int32 iflag, double *xx, char *serr);
        int32 swe_fixstar2_mag(char *star, double *mag, char *serr);
        void swe_close(void);
        void swe_set_ephe_path(char *path);
        void swe_set_jpl_file(char *fname);
        char * swe_get_planet_name(int ipl, char *spname);
        void swe_set_topo(double geolon, double geolat, double geoalt);
        void swe_set_sid_mode(int32 sid_mode, double t0, double ayan_t0);
        int32 swe_get_ayanamsa_ex(double tjd_et, int32 iflag, double *daya, char *serr);
        int32 swe_get_ayanamsa_ex_ut(double tjd_ut, int32 iflag, double *daya, char *serr);
        double swe_get_ayanamsa(double tjd_et);
        double swe_get_ayanamsa_ut(double tjd_ut);
        char * swe_get_ayanamsa_name(int32 isidmode);
        char * swe_get_current_file_data(int ifno, double *tfstart, double *tfend, int *denum);
        int swe_date_conversion(int y, int m, int d, double utime, char c, double *tjd);
        double swe_julday(int year, int month, int day, double hour, int gregflag);
        void swe_revjul(double jd, int gregflag, int *jyear, int *jmon, int *jday, double *jut);
        int32 swe_utc_to_jd(int32 iyear, int32 imonth, int32 iday, int32 ihour, int32 imin, double dsec, int32 gregflag, double *dret, char *serr);
        void swe_jdet_to_utc(double tjd_et, int32 gregflag, int32 *iyear, int32 *imonth, int32 *iday, int32 *ihour, int32 *imin, double *dsec);
        void swe_jdut1_to_utc(double tjd_ut, int32 gregflag, int32 *iyear, int32 *imonth, int32 *iday, int32 *ihour, int32 *imin, double *dsec);
        void swe_utc_time_zone(int32 iyear, int32 imonth, int32 iday, int32 ihour, int32 imin, double dsec, double d_timezone, int32 *iyear_out, int32 *imonth_out, int32 *iday_out, int32 *ihour_out, int32 *imin_out, double *dsec_out);
        int swe_houses(double tjd_ut, double geolat, double geolon, int hsys, double *cusps, double *ascmc);
        int swe_houses_ex(double tjd_ut, int32 iflag, double geolat, double geolon, int hsys, double *cusps, double *ascmc);
        int swe_houses_ex2(double tjd_ut, int32 iflag, double geolat, double geolon, int hsys, double *cusps, double *ascmc, double *cusp_speed, double *ascmc_speed, char *serr);
        int swe_houses_armc(double armc, double geolat, double eps, int hsys, double *cusps, double *ascmc);
        int swe_houses_armc_ex2(double armc, double geolat, double eps, int hsys, double *cusps, double *ascmc, double *cusp_speed, double *ascmc_speed, char *serr);
        double swe_house_pos(double armc, double geolat, double eps, int hsys, double *xpin, char *serr);
        char * swe_house_name(int hsys);
        int32 swe_gauquelin_sector(double t_ut, int32 ipl, char *starname, int32 iflag, int32 imeth, double *geopos, double atpress, double attemp, double *dgsect, char *serr);
        int32 swe_sol_eclipse_where(double tjd, int32 ifl, double *geopos, double *attr, char *serr);
        int32 swe_lun_occult_where(double tjd, int32 ipl, char *starname, int32 ifl, double *geopos, double *attr, char *serr);
        int32 swe_sol_eclipse_how(double tjd, int32 ifl, double *geopos, double *attr, char *serr);
        int32 swe_sol_eclipse_when_loc(double tjd_start, int32 ifl, double *geopos, double *tret, double *attr, int32 backward, char *serr);
        int32 swe_lun_occult_when_loc(double tjd_start, int32 ipl, char *starname, int32 ifl, double *geopos, double *tret, double *attr, int32 backward, char *serr);
        int32 swe_sol_eclipse_when_glob(double tjd_start, int32 ifl, int32 ifltype, double *tret, int32 backward, char *serr);
        int32 swe_lun_occult_when_glob(double tjd_start, int32 ipl, char *starname, int32 ifl, int32 ifltype, double *tret, int32 backward, char *serr);
        int32 swe_lun_eclipse_how(double tjd_ut, int32 ifl, double *geopos, double *attr, char *serr);
        int32 swe_lun_eclipse_when(double tjd_start, int32 ifl, int32 ifltype, double *tret, int32 backward, char *serr);
        int32 swe_lun_eclipse_when_loc(double tjd_start, int32 ifl, double *geopos, double *tret, double *attr, int32 backward, char *serr);
        int32 swe_pheno(double tjd, int32 ipl, int32 iflag, double *attr, char *serr);
        int32 swe_pheno_ut(double tjd_ut, int32 ipl, int32 iflag, double *attr, char *serr);
        double swe_refrac(double inalt, double atpress, double attemp, int32 calc_flag);
        double swe_refrac_extended(double inalt, double geoalt, double atpress, double attemp, double lapse_rate, int32 calc_flag, double *dret);
        void swe_set_lapse_rate(double lapse_rate);
        void swe_azalt(double tjd_ut, int32 calc_flag, double *geopos, double atpress, double attemp, double *xin, double *xaz);
        void swe_azalt_rev(double tjd_ut, int32 calc_flag, double *geopos, double *xin, double *xout);
        int32 swe_rise_trans_true_hor(double tjd_ut, int32 ipl, char *starname, int32 epheflag, int32 rsmi, double *geopos, double atpress, double attemp, double horhgt, double *tret, char *serr);
        int32 swe_rise_trans(double tjd_ut, int32 ipl, char *starname, int32 epheflag, int32 rsmi, double *geopos, double atpress, double attemp, double *tret, char *serr);
        int32 swe_nod_aps(double tjd_et, int32 ipl, int32 iflag, int32 method, double *xnasc, double *xndsc, double *xperi, double *xaphe, char *serr);
        int32 swe_nod_aps_ut(double tjd_ut, int32 ipl, int32 iflag, int32 method, double *xnasc, double *xndsc, double *xperi, double *xaphe, char *serr);
        int32 swe_get_orbital_elements(double tjd_et, int32 ipl, int32 iflag, double *dret, char *serr);
        int32 swe_orbit_max_min_true_distance(double tjd_et, int32 ipl, int32 iflag, double *dmax, double *dmin, double *dtrue, char *serr);
        double swe_deltat(double tjd);
        double swe_deltat_ex(double tjd, int32 iflag, char *serr);
        int32 swe_time_equ(double tjd, double *te, char *serr);
        int32 swe_lmt_to_lat(double tjd_lmt, double geolon, double *tjd_lat, char *serr);
        int32 swe_lat_to_lmt(double tjd_lat, double geolon, double *tjd_lmt, char *serr);
        double swe_sidtime0(double tjd_ut, double eps, double nut);
        double swe_sidtime(double tjd_ut);
        void swe_set_interpolate_nut(int32 do_interpolate);
        void swe_cotrans(double *xpo, double *xpn, double eps);
        void swe_cotrans_sp(double *xpo, double *xpn, double eps);
        double swe_get_tid_acc(void);
        void swe_set_tid_acc(double t_acc);
        void swe_set_delta_t_userdef(double dt);
        double swe_degnorm(double x);
        double swe_radnorm(double x);
        double swe_rad_midp(double x1, double x0);
        double swe_deg_midp(double x1, double x0);
        void swe_split_deg(double ddeg, int32 roundflag, int32 *ideg, int32 *imin, int32 *isec, double *dsecfr, int32 *isgn);
        int32 swe_csnorm(int32 p);
        int32 swe_difcsn(int32 p1, int32 p2);
        double swe_difdegn(double p1, double p2);
        int32 swe_difcs2n(int32 p1, int32 p2);
        double swe_difdeg2n(double p1, double p2);
        double swe_difrad2n(double p1, double p2);
        int32 swe_csroundsec(int32 x);
        int32 swe_d2l(double x);
        int swe_day_of_week(double jd);
        char * swe_cs2timestr(int32 t, int sep, int32 suppressZero, char *a);
        char * swe_cs2lonlatstr(int32 t, char pchar, char mchar, char *s);
        char * swe_cs2degstr(int32 t, char *a);
        CDEF;
    }
}
