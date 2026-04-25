# Swiss Ephemeris Version Tracking

This document tracks the Swiss Ephemeris C library version used in this PHP FFI wrapper.

## Current Version

| Attribute | Value |
|-----------|-------|
| **Upstream Repository** | [aloistr/swisseph](https://github.com/aloistr/swisseph) |
| **Latest Release Tag** | v2.10.3final (April 2026) |
| **Development Branch** | `master` (actively maintained) |
| **Last Upstream Commit** | April 18, 2026 |
| **Latest Commit** | `af9823f` |
| **Latest Commit Message** | "now created with DE441" |

## Version History

### v2.10.3final (Current)
- **Release Date**: April 11, 2026
- **Tag**: `v2.10.3final`
- **Commit**: `af9823f`
- **Changes**: 
  - Updated ephemeris data (seasnam.txt expanded significantly).
  - Minor build and comment updates in headers.
  - Internal version string remains `2.10.03`.

### v2.10.03
- **Release Date**: September 9, 2022
- **Tag**: `v2.10.03`
- **Commit**: `175e1fcb3108bcd5c0d146c803f51dcf23508012`

### Recent Development Updates (2026)
- **2026-03-11**: Fixed old rounding bug in `swe_split_deg()`
- **2026-03-01**: `roundmin` now observed in output field `l`
- **2026-03-01**: `swe_set_ephe_path` now has `serr` parameter for debugging

## How to Update

This package always uses the **latest version** from the upstream Swiss Ephemeris repository when you rebuild the library:

```bash
# Rebuild with latest upstream version
composer build

# Or manually
bash build/compile.sh
```

The build script:
1. Clones/pulls the latest from `https://github.com/aloistr/swisseph`
2. Compiles the C source into `libswe.so`
3. Places the shared library in `build/libswe.so`

## Checking Your Version

To check which version of Swiss Ephemeris you're using:

```bash
# Check the commit hash of your local copy
cd build/swisseph_src && git log -1 --oneline

# Or check the library version at runtime
php -r "
require 'vendor/autoload.php';
\$sweph = new SwissEph\FFI\SwissEphFFI();
echo \$sweph->swe_version() . PHP_EOL;
"
```

## Upstream Repository Links

- **GitHub**: [aloistr/swisseph](https://github.com/aloistr/swisseph)
- **Releases**: [Swiss Ephemeris releases](https://github.com/aloistr/swisseph/releases)
- **Commits**: [master branch commits](https://github.com/aloistr/swisseph/commits/master)
- **Official Site**: [astro.com Swiss Ephemeris](https://www.astro.com/swisseph/)

## License Compatibility

This repository is licensed under **AGPL-3.0-or-later**.

The upstream Swiss Ephemeris C library and related ephemeris files remain subject to the Swiss Ephemeris upstream licensing model as well.

If you use Swiss Ephemeris in commercial software, or redistribute upstream binaries/data, review the commercial licensing terms from [Astrodienst](https://www.astro.com/swisseph/swephprice_e.htm).
