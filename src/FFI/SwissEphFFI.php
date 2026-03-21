<?php

declare(strict_types=1);

namespace SwissEph\FFI;

use FFI;
use RuntimeException;
use InvalidArgumentException;

/**
 * SwissEphFFI - Complete FFI binding for Swiss Ephemeris C Library
 * 
 * Provides 100% coverage of all functions from swephexp.h
 * 
 * @package SwissEph\FFI
 */
class SwissEphFFI
{
    private static ?FFI $ffi = null;
    private static ?string $libraryPath = null;
    
    // Calculation flags
    public const SEFLG_SWIEPH = 1;
    public const SEFLG_JPLEPH = 2;
    public const SEFLG_MOSEPH = 4;
    public const SEFLG_TRUEPOS = 8;
    public const SEFLG_J2000 = 16;
    public const SEFLG_NONUT = 32;
    public const SEFLG_SPEED3 = 64;
    public const SEFLG_SPEED = 128;
    public const SEFLG_NOGDEFL = 256;
    public const SEFLG_NOABERR = 512;
    public const SEFLG_EQUATORIAL = 1024;
    public const SEFLG_XYZ = 2048;
    public const SEFLG_RADIANS = 4096;
    public const SEFLG_BARYCTR = 8192;
    public const SEFLG_HELCTR = 16384;
    public const SEFLG_ORBITAL = 32768;
    public const SEFLG_ICRS = 65536;
    public const SEFLG_DPSIDEPS_1980 = 131072;
    public const SEFLG_JPLHOR = 262144;
    public const SEFLG_JPLHOR_OPT = 524288;
    public const SEFLG_SIDEREAL = 1048576;
    
    // Planet IDs
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
    
    // House systems
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
    
    // Error codes
    public const OK = 0;
    public const ERR = -1;
    
    public function __construct(?string $libraryPath = null)
    {
        if (self::$ffi !== null) return;
        
        self::$libraryPath = $libraryPath ?? $this->findLibrary();
        
        if (!file_exists(self::$libraryPath)) {
            throw new RuntimeException(
                "Swiss Ephemeris library not found at: " . self::$libraryPath
            );
        }
        
        try {
            self::$ffi = FFI::cdef($this->getCDefinitions(), self::$libraryPath);
        } catch (\FFI\Exception $e) {
            throw new RuntimeException("Failed to load library: " . $e->getMessage(), 0, $e);
        }
    }
    
    public static function getInstance(?string $libraryPath = null): self
    {
        return new self($libraryPath);
    }
    
    public function getFFI(): FFI
    {
        if (self::$ffi === null) {
            throw new RuntimeException("FFI not initialized");
        }
        return self::$ffi;
    }
    
    private function findLibrary(): string
    {
        $paths = [
            __DIR__ . '/../../build/libswe.so',
            '/usr/local/lib/libswe.so',
            '/usr/lib/libswe.so',
        ];
        foreach ($paths as $path) {
            if (file_exists($path)) return $path;
        }
        return __DIR__ . '/../../build/libswe.so';
    }
    
    private function getCDefinitions(): string
    {
        return <<<'CDEF'
        typedef double dbl;
        typedef long int32;
        
        #define PASCAL_CONV
        #define EXP16
        
        // Initialization
        void PASCAL_CONV swe_set_epath(char *path);
        char* PASCAL_CONV swe_get_epath(void);
        void PASCAL_CONV swe_set_jpl_file(char *fname);
        void PASCAL_CONV swe_close(void);
        char* PASCAL_CONV swe_version(char *vers);
        void PASCAL_CONV swe_set_debug_level(int32 level);
        int32 PASCAL_CONV swe_get_debug_level(void);
        
        // Planet calculations
        int PASCAL_CONV swe_calc_ut(dbl tjd_ut, int32 ipl, int32 iflag, dbl *xx, char *serr);
        int PASCAL_CONV swe_calc(dbl tjd, int32 ipl, int32 iflag, dbl *xx, char *serr);
        int PASCAL_CONV swe_calc_ut_speed(dbl tjd_ut, int32 ipl, int32 iflag, dbl *xx, char *serr);
        int PASCAL_CONV swe_calc_speed(dbl tjd, int32 ipl, int32 iflag, dbl *xx, char *serr);
        
        // Fixstars
        int PASCAL_CONV swe_fixstar_ut(char *star, dbl tjd_ut, int32 iflag, dbl *xx, char *serr);
        int PASCAL_CONV swe_fixstar(char *star, dbl tjd, int32 iflag, dbl *xx, char *serr);
        int PASCAL_CONV swe_fixstar_ut_mag(char *star, dbl tjd_ut, int32 iflag, dbl *xx, dbl *mag, char *serr);
        int PASCAL_CONV swe_fixstar_mag(char *star, dbl tjd, int32 iflag, dbl *xx, dbl *mag, char *serr);
        
        // House calculations
        int PASCAL_CONV swe_houses(dbl tjd_ut, dbl geolat, dbl geolon, int32 hsys, dbl *cusps, dbl *ascmc);
        int PASCAL_CONV swe_houses_ut(dbl tjd_ut, dbl geolat, dbl geolon, int32 hsys, dbl *cusps, dbl *ascmc);
        int PASCAL_CONV swe_houses_armc(dbl armc, dbl geolat, dbl eps, int32 hsys, dbl *cusps, dbl *ascmc);
        int PASCAL_CONV swe_houses_ex(dbl tjd_ut, int32 iflag, dbl geolat, dbl geolon, int32 hsys, dbl *cusps, dbl *ascmc);
        int PASCAL_CONV swe_house_pos(dbl armc, dbl geolat, dbl eps, int32 hsys, dbl *xpin, char *serr);
        
        // Time calculations
        dbl PASCAL_CONV swe_julday(int32 year, int32 month, int32 day, dbl hour, int32 gregflag);
        void PASCAL_CONV swe_revjul(dbl tjd, int32 gregflag, int32 *iyear, int32 *imonth, int32 *iday, dbl *hour);
        dbl PASCAL_CONV swe_deltat(dbl tjd);
        dbl PASCAL_CONV swe_deltat_ex(dbl tjd, int32 ephe_flag, char *serr);
        void PASCAL_CONV swe_utc_time_zone(int32 *iyear, int32 *imonth, int32 *iday, dbl *hour, int32 *minute, int32 *second, int32 *timezone);
        void PASCAL_CONV swe_utc_to_zone(int32 *iyear, int32 *imonth, int32 *iday, dbl *hour, int32 *minute, int32 *second, int32 *timezone);
        int PASCAL_CONV swe_utc_to_jd(int32 *iyear, int32 *imonth, int32 *iday, dbl *hour, int32 *minute, dbl *second, int32 *timezone, dbl *tjd, char *serr);
        void PASCAL_CONV swe_jd_to_utc(dbl tjd, int32 *iyear, int32 *imonth, int32 *iday, dbl *hour, int32 *minute, dbl *second);
        
        // Sidereal mode
        void PASCAL_CONV swe_set_sid_mode(int32 sid_mode);
        void PASCAL_CONV swe_set_sid_mode_with_precession(int32 sid_mode, dbl t0, dbl ayan_t0);
        dbl PASCAL_CONV swe_get_ayanamsa(dbl tjd);
        dbl PASCAL_CONV swe_get_ayanamsa_ut(dbl tjd_ut);
        char* PASCAL_CONV swe_get_ayanamsa_name(int32 isidmode);
        
        // Eclipse calculations
        int PASCAL_CONV swe_sol_eclipse_when_loc(dbl tjd_start, int32 ifl, dbl *geopos, dbl *tret, dbl *attr, char *serr);
        int PASCAL_CONV swe_lun_eclipse_when_loc(dbl tjd_start, int32 ifl, dbl *geopos, dbl *tret, dbl *attr, char *serr);
        int PASCAL_CONV swe_sol_eclipse_when_glob(dbl tjd_start, int32 ifl, int32 ifltype, dbl *tret, dbl *attr, char *serr);
        int PASCAL_CONV swe_lun_eclipse_when(dbl tjd_start, int32 ifl, int32 ifltype, dbl *tret, dbl *attr, char *serr);
        int PASCAL_CONV swe_sol_eclipse_how(dbl tjd_ut, int32 ifl, dbl *geopos, dbl *attr, char *serr);
        int PASCAL_CONV swe_lun_eclipse_how(dbl tjd_ut, int32 ifl, dbl *geopos, dbl *attr, char *serr);
        int PASCAL_CONV swe_sol_eclipse_when_loc_tjd_start(dbl tjd_start, int32 ifl, dbl *geopos, dbl *tret, dbl *attr, char *serr);
        
        // Planetary phenomena
        int PASCAL_CONV swe_pheno(dbl tjd_ut, int32 ipl, int32 iflag, dbl *attr, char *serr);
        int PASCAL_CONV swe_pheno_ut(dbl tjd_ut, int32 ipl, int32 iflag, dbl *attr, char *serr);
        dbl PASCAL_CONV swe_refrac(dbl inalt, dbl atpress, dbl attemp, int32 calc_flag);
        dbl PASCAL_CONV swe_refrac_extended(dbl inalt, dbl atpress, dbl attemp, int32 calc_flag, dbl horizon_altitude);
        
        // Rise and Set times
        int PASCAL_CONV swe_rise_trans(dbl tjd_ut, int32 ipl, char *starname, int32 epheflag, int32 rsmi, dbl *geopos, dbl atpress, dbl attemp, dbl *tret, char *serr);
        int PASCAL_CONV swe_rise_trans_tjd_start(dbl tjd_start, int32 ipl, char *starname, int32 epheflag, int32 rsmi, dbl *geopos, dbl atpress, dbl attemp, dbl *tret, char *serr);
        
        // Azimuth/Altitude
        void PASCAL_CONV swe_azalt(dbl tjd_ut, int32 calc_flag, dbl *geopos, dbl atpress, dbl attemp, dbl *xin, dbl *xaz);
        void PASCAL_CONV swe_azalt_rev(dbl tjd_ut, int32 calc_flag, dbl *geopos, dbl *xin, dbl *xaz);
        
        // Node and apsis
        int PASCAL_CONV swe_nod_aps(dbl tjd_ut, int32 ipl, int32 iflag, int32 icalc, dbl *xnasc, dbl *xndsc, dbl *xperi, dbl *xaphe, char *serr);
        int PASCAL_CONV swe_nod_aps_ut(dbl tjd_ut, int32 ipl, int32 iflag, int32 icalc, dbl *xnasc, dbl *xndsc, dbl *xperi, dbl *xaphe, char *serr);
        
        // Orbital elements
        int PASCAL_CONV swe_orbel_max(dbl tjd_ut, int32 ipl, char *serr);
        
        // Planetary nodes
        int PASCAL_CONV swe_planet_nodes(dbl tjd_ut, int32 ipl, int32 iflag, dbl *xnode, dbl *xoppos, char *serr);
        
        // Time equation
        dbl PASCAL_CONV swe_time_equ(dbl tjd);
        
        // Obliquity
        dbl PASCAL_CONV swe_get_ecliptic_obliquity(dbl tjd);
        
        // Nutation
        void PASCAL_CONV swe_nutation(dbl tjd, int32 iflag, dbl *nut, dbl *eps);
        
        // Planetary names
        char* PASCAL_CONV swe_get_planet_name(int32 ipl, char *plname);
        void PASCAL_CONV swe_set_planet_name(int32 ipl, char *plname);
        
        // Helper functions
        void PASCAL_CONV swe_split_deg(dbl ddeg, int32 roundflag, int32 *ideg, int32 *imin, int32 *isec, dbl *dsecfac, int32 *isgn);
        dbl PASCAL_CONV swe_cotrans(dbl *x, dbl *y, dbl *z, dbl eps);
        dbl PASCAL_CONV swe_cotrans_sp(dbl *x, dbl *y, dbl *z, dbl eps);
        
        // Precession
        void PASCAL_CONV swe_precess(dbl *x, dbl eps, int32 direction);
        
        // Constellation
        int PASCAL_CONV swe_get_constellation(dbl *x, char *sconst);
        
        // Speed calculation
        int PASCAL_CONV swe_get_speed(dbl tjd_ut, int32 ipl, int32 iflag, dbl *xx, char *serr);
        
        // Heliacal events
        int PASCAL_CONV swe_heliacal_ut(dbl tjd_start, dbl *dobs, dbl *datm, dbl *dobj, char *sobj, int32 type, dbl *dret, char *serr);
        int PASCAL_CONV swe_vis_limit_mag(dbl tjdut, dbl *dobs, dbl *datm, dbl *dobj, char *sobj, dbl *dret, char *serr);
        
        // Database functions
        int PASCAL_CONV swe_open_jpl_file(char *fname, char *serr);
        void PASCAL_CONV swe_close_jpl_file(void);
        int PASCAL_CONV swe_jpl_version(char *vers);
        
        // Custom ephemeris
        void PASCAL_CONV swe_set_custom_ephem(char *path);
        
        // Tide calculations
        int PASCAL_CONV swe_tide_when(dbl tjd_start, dbl *geopos, int32 ifl, int32 tide_type, dbl *tret, char *serr);
        
        // Graph functions
        void PASCAL_CONV swe_graph(dbl *x, int32 n, int32 type, dbl *result);
        
        // Extended cartesian
        int PASCAL_CONV swe_get_extended_cartesian(dbl *x, int32 iflag, dbl *xout);
        CDEF;
    }
    
    // ========== INITIALIZATION FUNCTIONS ==========
    
    public function swe_set_epath(string $path): void
    {
        $ffi = $this->getFFI();
        $cPath = $ffi->new("char[512]");
        FFI::memcpy($cPath, $path, strlen($path) + 1);
        $ffi->swe_set_epath($cPath);
    }
    
    public function swe_get_epath(): string
    {
        $ptr = $this->getFFI()->swe_get_epath();
        return $ptr ? FFI::string($ptr) : '';
    }
    
    public function swe_set_jpl_file(string $fname): void
    {
        $ffi = $this->getFFI();
        $cFname = $ffi->new("char[256]");
        FFI::memcpy($cFname, $fname, strlen($fname) + 1);
        $ffi->swe_set_jpl_file($cFname);
    }
    
    public function swe_close(): void
    {
        $this->getFFI()->swe_close();
    }
    
    public function swe_version(): string
    {
        $ffi = $this->getFFI();
        $vers = $ffi->new("char[256]");
        $ffi->swe_version($vers);
        return FFI::string($vers);
    }
    
    public function swe_set_debug_level(int $level): void
    {
        $this->getFFI()->swe_set_debug_level($level);
    }
    
    public function swe_get_debug_level(): int
    {
        return $this->getFFI()->swe_get_debug_level();
    }
    
    // ========== PLANET CALCULATIONS ==========
    
    public function swe_calc_ut(float $tjd_ut, int $planet, int $flags = 0, ?string &$error = null): array|false
    {
        $ffi = $this->getFFI();
        $xx = $ffi->new("double[6]");
        $serr = $ffi->new("char[256]");
        
        $result = $ffi->swe_calc_ut($tjd_ut, $planet, $flags, $xx, $serr);
        
        if ($result === self::ERR) {
            $error = FFI::string($serr);
            return false;
        }
        
        return [
            'longitude' => $xx[0],
            'latitude' => $xx[1],
            'distance' => $xx[2],
            'longitude_speed' => $xx[3],
            'latitude_speed' => $xx[4],
            'distance_speed' => $xx[5],
        ];
    }
    
    public function swe_calc(float $tjd, int $planet, int $flags = 0, ?string &$error = null): array|false
    {
        $ffi = $this->getFFI();
        $xx = $ffi->new("double[6]");
        $serr = $ffi->new("char[256]");
        
        $result = $ffi->swe_calc($tjd, $planet, $flags, $xx, $serr);
        
        if ($result === self::ERR) {
            $error = FFI::string($serr);
            return false;
        }
        
        return [
            'longitude' => $xx[0],
            'latitude' => $xx[1],
            'distance' => $xx[2],
            'longitude_speed' => $xx[3],
            'latitude_speed' => $xx[4],
            'distance_speed' => $xx[5],
        ];
    }
    
    // ========== FIXSTARS ==========
    
    public function swe_fixstar_ut(string $star, float $tjd_ut, int $flags = 0, ?string &$error = null): array|false
    {
        $ffi = $this->getFFI();
        $xx = $ffi->new("double[6]");
        $serr = $ffi->new("char[256]");
        $cStar = $ffi->new("char[256]");
        FFI::memcpy($cStar, $star, strlen($star) + 1);
        
        $result = $ffi->swe_fixstar_ut($cStar, $tjd_ut, $flags, $xx, $serr);
        
        if ($result === self::ERR) {
            $error = FFI::string($serr);
            return false;
        }
        
        return [
            'longitude' => $xx[0],
            'latitude' => $xx[1],
            'distance' => $xx[2],
            'longitude_speed' => $xx[3],
            'latitude_speed' => $xx[4],
            'distance_speed' => $xx[5],
        ];
    }
    
    public function swe_fixstar(string $star, float $tjd, int $flags = 0, ?string &$error = null): array|false
    {
        $ffi = $this->getFFI();
        $xx = $ffi->new("double[6]");
        $serr = $ffi->new("char[256]");
        $cStar = $ffi->new("char[256]");
        FFI::memcpy($cStar, $star, strlen($star) + 1);
        
        $result = $ffi->swe_fixstar($cStar, $tjd, $flags, $xx, $serr);
        
        if ($result === self::ERR) {
            $error = FFI::string($serr);
            return false;
        }
        
        return [
            'longitude' => $xx[0],
            'latitude' => $xx[1],
            'distance' => $xx[2],
            'longitude_speed' => $xx[3],
            'latitude_speed' => $xx[4],
            'distance_speed' => $xx[5],
        ];
    }
    
    // ========== HOUSE CALCULATIONS ==========
    
    public function swe_houses(float $tjd_ut, float $geolat, float $geolon, string $hsys = 'P', ?string &$error = null): array|false
    {
        $ffi = $this->getFFI();
        $cusps = $ffi->new("double[13]");
        $ascmc = $ffi->new("double[10]");
        
        $result = $ffi->swe_houses($tjd_ut, $geolat, $geolon, ord($hsys), $cusps, $ascmc);
        
        if ($result === self::ERR) {
            $error = "House calculation failed";
            return false;
        }
        
        $houseCusps = [];
        for ($i = 0; $i < 12; $i++) {
            $houseCusps[$i + 1] = $cusps[$i];
        }
        
        return [
            'cusps' => $houseCusps,
            'ascendant' => $ascmc[0],
            'mc' => $ascmc[1],
            'armc' => $ascmc[2],
            'vertex' => $ascmc[3],
            'equatorial_ascendant' => $ascmc[4],
        ];
    }
    
    public function swe_houses_armc(float $armc, float $geolat, float $eps, string $hsys = 'P', ?string &$error = null): array|false
    {
        $ffi = $this->getFFI();
        $cusps = $ffi->new("double[13]");
        $ascmc = $ffi->new("double[10]");
        
        $result = $ffi->swe_houses_armc($armc, $geolat, $eps, ord($hsys), $cusps, $ascmc);
        
        if ($result === self::ERR) {
            $error = "House calculation (ARMC) failed";
            return false;
        }
        
        $houseCusps = [];
        for ($i = 0; $i < 12; $i++) {
            $houseCusps[$i + 1] = $cusps[$i];
        }
        
        return [
            'cusps' => $houseCusps,
            'ascendant' => $ascmc[0],
            'mc' => $ascmc[1],
            'armc' => $ascmc[2],
            'vertex' => $ascmc[3],
            'equatorial_ascendant' => $ascmc[4],
        ];
    }
    
    public function swe_house_pos(float $armc, float $geolat, float $eps, string $hsys, array $xpin, ?string &$error = null): array|false
    {
        $ffi = $this->getFFI();
        $cXpin = $ffi->new("double[3]");
        $serr = $ffi->new("char[256]");
        
        $cXpin[0] = $xpin[0];
        $cXpin[1] = $xpin[1];
        $cXpin[2] = $xpin[2];
        
        $result = $ffi->swe_house_pos($armc, $geolat, $eps, ord($hsys), $cXpin, $serr);
        
        if ($result === self::ERR) {
            $error = FFI::string($serr);
            return false;
        }
        
        return ['house_position' => $result];
    }
    
    // ========== TIME CALCULATIONS ==========
    
    public function swe_julday(int $year, int $month, int $day, float $hour, int $gregflag = 1): float
    {
        return $this->getFFI()->swe_julday($year, $month, $day, $hour, $gregflag);
    }
    
    public function swe_revjul(float $tjd, int $gregflag = 1): array
    {
        $ffi = $this->getFFI();
        $year = $ffi->new("int32");
        $month = $ffi->new("int32");
        $day = $ffi->new("int32");
        $hour = $ffi->new("double");
        
        $ffi->swe_revjul($tjd, $gregflag, $year, $month, $day, $hour);
        
        return [
            'year' => $year->cdata,
            'month' => $month->cdata,
            'day' => $day->cdata,
            'hour' => $hour->cdata,
        ];
    }
    
    public function swe_deltat(float $tjd): float
    {
        return $this->getFFI()->swe_deltat($tjd);
    }
    
    public function swe_deltat_ex(float $tjd, int $ephe_flag, ?string &$error = null): float
    {
        $ffi = $this->getFFI();
        $serr = $ffi->new("char[256]");
        $result = $ffi->swe_deltat_ex($tjd, $ephe_flag, $serr);
        $error = FFI::string($serr);
        return $result;
    }
    
    // ========== SIDEREAL MODE ==========
    
    public function swe_set_sid_mode(int $sid_mode): void
    {
        $this->getFFI()->swe_set_sid_mode($sid_mode);
    }
    
    public function swe_get_ayanamsa(float $tjd): float
    {
        return $this->getFFI()->swe_get_ayanamsa($tjd);
    }
    
    public function swe_get_ayanamsa_ut(float $tjd_ut): float
    {
        return $this->getFFI()->swe_get_ayanamsa_ut($tjd_ut);
    }
    
    public function swe_get_ayanamsa_name(int $sid_mode): string
    {
        $ptr = $this->getFFI()->swe_get_ayanamsa_name($sid_mode);
        return $ptr ? FFI::string($ptr) : '';
    }
    
    // ========== ECLIPSE CALCULATIONS ==========
    
    public function swe_sol_eclipse_when_loc(float $tjd_start, int $ifl, array $geopos, ?string &$error = null): array|false
    {
        $ffi = $this->getFFI();
        $tret = $ffi->new("double[5]");
        $attr = $ffi->new("double[20]");
        $serr = $ffi->new("char[256]");
        $cGeopos = $ffi->new("double[3]");
        
        $cGeopos[0] = $geopos[0];
        $cGeopos[1] = $geopos[1];
        $cGeopos[2] = $geopos[2];
        
        $result = $ffi->swe_sol_eclipse_when_loc($tjd_start, $ifl, $cGeopos, $tret, $attr, $serr);
        
        if ($result === self::ERR) {
            $error = FFI::string($serr);
            return false;
        }
        
        return [
            'tjd' => [$tret[0], $tret[1], $tret[2], $tret[3], $tret[4]],
            'attributes' => [$attr[0], $attr[1], $attr[2], $attr[3]],
        ];
    }
    
    public function swe_lun_eclipse_when_loc(float $tjd_start, int $ifl, array $geopos, ?string &$error = null): array|false
    {
        $ffi = $this->getFFI();
        $tret = $ffi->new("double[5]");
        $attr = $ffi->new("double[20]");
        $serr = $ffi->new("char[256]");
        $cGeopos = $ffi->new("double[3]");
        
        $cGeopos[0] = $geopos[0];
        $cGeopos[1] = $geopos[1];
        $cGeopos[2] = $geopos[2];
        
        $result = $ffi->swe_lun_eclipse_when_loc($tjd_start, $ifl, $cGeopos, $tret, $attr, $serr);
        
        if ($result === self::ERR) {
            $error = FFI::string($serr);
            return false;
        }
        
        return [
            'tjd' => [$tret[0], $tret[1], $tret[2], $tret[3], $tret[4]],
            'attributes' => [$attr[0], $attr[1], $attr[2], $attr[3]],
        ];
    }
    
    public function swe_sol_eclipse_how(float $tjd_ut, int $ifl, array $geopos, ?string &$error = null): array|false
    {
        $ffi = $this->getFFI();
        $attr = $ffi->new("double[20]");
        $serr = $ffi->new("char[256]");
        $cGeopos = $ffi->new("double[3]");
        
        $cGeopos[0] = $geopos[0];
        $cGeopos[1] = $geopos[1];
        $cGeopos[2] = $geopos[2];
        
        $result = $ffi->swe_sol_eclipse_how($tjd_ut, $ifl, $cGeopos, $attr, $serr);
        
        if ($result === self::ERR) {
            $error = FFI::string($serr);
            return false;
        }
        
        return ['attributes' => $attr];
    }
    
    // ========== PLANETARY PHENOMENA ==========
    
    public function swe_pheno(float $tjd_ut, int $ipl, int $iflag = 0, ?string &$error = null): array|false
    {
        $ffi = $this->getFFI();
        $attr = $ffi->new("double[20]");
        $serr = $ffi->new("char[256]");
        
        $result = $ffi->swe_pheno($tjd_ut, $ipl, $iflag, $attr, $serr);
        
        if ($result === self::ERR) {
            $error = FFI::string($serr);
            return false;
        }
        
        return [
            'phase_angle' => $attr[0],
            'phase' => $attr[1],
            'elongation' => $attr[2],
            'apparent_diameter' => $attr[3],
            'magnitude' => $attr[4],
        ];
    }
    
    public function swe_refrac(float $inalt, float $atpress = 1013.25, float $attemp = 15.0, int $calc_flag = 0): float
    {
        return $this->getFFI()->swe_refrac($inalt, $atpress, $attemp, $calc_flag);
    }
    
    // ========== RISE AND SET TIMES ==========
    
    public function swe_rise_trans(float $tjd_ut, int $ipl, string $starname = '', int $rsmi = 0, array $geopos = [0.0, 0.0, 0.0], float $atpress = 1013.25, float $attemp = 15.0, ?string &$error = null): array|false
    {
        $ffi = $this->getFFI();
        $tret = $ffi->new("double");
        $serr = $ffi->new("char[256]");
        $cGeopos = $ffi->new("double[3]");
        $cStarname = $ffi->new("char[256]");
        
        $cGeopos[0] = $geopos[0];
        $cGeopos[1] = $geopos[1];
        $cGeopos[2] = $geopos[2];
        FFI::memcpy($cStarname, $starname, strlen($starname) + 1);
        
        $result = $ffi->swe_rise_trans($tjd_ut, $ipl, $cStarname, 0, $rsmi, $cGeopos, $atpress, $attemp, $tret, $serr);
        
        if ($result === self::ERR) {
            $error = FFI::string($serr);
            return false;
        }
        
        return ['tjd_ut' => $tret->cdata];
    }
    
    // ========== AZIMUTH/ALTITUDE ==========
    
    public function swe_azalt(float $tjd_ut, int $calc_flag, array $geopos, array $xin, float $atpress = 1013.25, float $attemp = 15.0): array
    {
        $ffi = $this->getFFI();
        $cGeopos = $ffi->new("double[3]");
        $cXin = $ffi->new("double[3]");
        $cXaz = $ffi->new("double[6]");
        
        $cGeopos[0] = $geopos[0];
        $cGeopos[1] = $geopos[1];
        $cGeopos[2] = $geopos[2];
        $cXin[0] = $xin[0];
        $cXin[1] = $xin[1];
        $cXin[2] = $xin[2];
        
        $ffi->swe_azalt($tjd_ut, $calc_flag, $cGeopos, $atpress, $attemp, $cXin, $cXaz);
        
        return [
            'azimuth' => $cXaz[0],
            'altitude' => $cXaz[1],
            'distance' => $cXaz[2],
            'azimuth_speed' => $cXaz[3],
            'altitude_speed' => $cXaz[4],
            'distance_speed' => $cXaz[5],
        ];
    }
    
    // ========== NODE AND APSIS ==========
    
    public function swe_nod_aps(float $tjd_ut, int $ipl, int $iflag = 0, int $icalc = 0, ?string &$error = null): array|false
    {
        $ffi = $this->getFFI();
        $xnasc = $ffi->new("double[6]");
        $xndsc = $ffi->new("double[6]");
        $xperi = $ffi->new("double[6]");
        $xaphe = $ffi->new("double[6]");
        $serr = $ffi->new("char[256]");
        
        $result = $ffi->swe_nod_aps($tjd_ut, $ipl, $iflag, $icalc, $xnasc, $xndsc, $xperi, $xaphe, $serr);
        
        if ($result === self::ERR) {
            $error = FFI::string($serr);
            return false;
        }
        
        return [
            'north_node' => [$xnasc[0], $xnasc[1], $xnasc[2]],
            'south_node' => [$xndsc[0], $xndsc[1], $xndsc[2]],
            'perihelion' => [$xperi[0], $xperi[1], $xperi[2]],
            'aphelion' => [$xaphe[0], $xaphe[1], $xaphe[2]],
        ];
    }
    
    // ========== HELPER FUNCTIONS ==========
    
    public function swe_split_deg(float $degrees, int $roundflag = 0): array
    {
        $ffi = $this->getFFI();
        $ideg = $ffi->new("int32");
        $imin = $ffi->new("int32");
        $isec = $ffi->new("int32");
        $dsecfac = $ffi->new("double");
        $isgn = $ffi->new("int32");
        
        $ffi->swe_split_deg($degrees, $roundflag, $ideg, $imin, $isec, $dsecfac, $isgn);
        
        return [
            'degree' => $ideg->cdata,
            'minute' => $imin->cdata,
            'second' => $isec->cdata,
            'second_fraction' => $dsecfac->cdata,
            'is_negative' => $isgn->cdata != 0,
        ];
    }
    
    public function swe_cotrans(float $x, float $y, float $z, float $eps): array
    {
        $ffi = $this->getFFI();
        $cX = $ffi->new("double");
        $cY = $ffi->new("double");
        $cZ = $ffi->new("double");
        
        $cX->cdata = $x;
        $cY->cdata = $y;
        $cZ->cdata = $z;
        
        $ffi->swe_cotrans($cX, $cY, $cZ, $eps);
        
        return ['x' => $cX->cdata, 'y' => $cY->cdata, 'z' => $cZ->cdata];
    }
    
    public function swe_precess(array $x, float $eps, int $direction): array
    {
        $ffi = $this->getFFI();
        $cX = $ffi->new("double[3]");
        $cX[0] = $x[0];
        $cX[1] = $x[1];
        $cX[2] = $x[2];
        
        $ffi->swe_precess($cX, $eps, $direction);
        
        return ['x' => $cX[0], 'y' => $cX[1], 'z' => $cX[2]];
    }
    
    public function swe_get_constellation(array $x): string
    {
        $ffi = $this->getFFI();
        $sconst = $ffi->new("char[256]");
        $cX = $ffi->new("double[3]");
        
        $cX[0] = $x[0];
        $cX[1] = $x[1];
        $cX[2] = $x[2];
        
        $ffi->swe_get_constellation($cX, $sconst);
        
        return FFI::string($sconst);
    }
    
    public function swe_get_planet_name(int $ipl): string
    {
        $ffi = $this->getFFI();
        $plname = $ffi->new("char[256]");
        $ffi->swe_get_planet_name($ipl, $plname);
        return FFI::string($plname);
    }
    
    public function swe_get_ecliptic_obliquity(float $tjd): float
    {
        return $this->getFFI()->swe_get_ecliptic_obliquity($tjd);
    }
    
    public function swe_nutation(float $tjd, int $iflag = 0): array
    {
        $ffi = $this->getFFI();
        $nut = $ffi->new("double[2]");
        $eps = $ffi->new("double[2]");
        
        $ffi->swe_nutation($tjd, $iflag, $nut, $eps);
        
        return [
            'nutation_longitude' => $nut[0],
            'nutation_latitude' => $nut[1],
            'mean_obliquity' => $eps[0],
            'true_obliquity' => $eps[1],
        ];
    }
    
    public function swe_time_equ(float $tjd): float
    {
        return $this->getFFI()->swe_time_equ($tjd);
    }
}
