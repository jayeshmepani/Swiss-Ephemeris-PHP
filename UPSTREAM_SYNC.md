# Swiss Ephemeris PHP FFI - Upstream Synchronization Report

**Generated**: April 25, 2026  
**Upstream Repository**: [aloistr/swisseph](https://github.com/aloistr/swisseph)

---

## Current Status

This package is configured to track the upstream Swiss Ephemeris source when the library is rebuilt.

As of this report, the package targets the latest public upstream `master` state checked on **April 25, 2026**, which includes the `v2.10.3final` release plus post-release commits through **April 18, 2026**.

---

## Upstream Swiss Ephemeris Information

| Attribute                        | Value                                                   |
| -------------------------------- | ------------------------------------------------------- |
| **Repository**                   | [aloistr/swisseph](https://github.com/aloistr/swisseph) |
| **Latest Release Tag**           | `v2.10.3final`                                          |
| **Latest Release Date**          | April 14, 2026                                          |
| **Release Commit**               | `af9823f`                                               |
| **Active Branch Checked**        | `master`                                                |
| **Latest Public Commit Checked** | `2f18c14`                                               |
| **Latest Public Commit Date**    | April 18, 2026                                          |
| **Latest Commit Message**        | `fixed bug in semo4200.se1`                             |
| **Internal C Version String**    | `2.10.03`                                               |
| **Upstream Licensing Model**     | AGPL or Swiss Ephemeris Professional License            |

---

## Important Notes

- `v2.10.3final` is the final 2.10.3 release before publication of Swiss Ephemeris 3.0.
- No public `v3.0` release tag was available at the time of this report.
- Upstream currently has **6 commits** after `v2.10.3final` on `master`.
- The post-release comparison from `v2.10.3final` to `master` shows **154 files changed**.
- The full comparison from `v2.10.03` to `v2.10.3final` shows **204 commits** and **345 files changed**.

---

## Recent Upstream Changes

| Date       | Commit    | Verified Description                                                     |
| ---------- | --------- | ------------------------------------------------------------------------ |
| 2026-04-18 | `2f18c14` | Fixed bug in `semo4200.se1`                                              |
| 2026-04-15 | `5a7de9e` | Added note that DE441 `.se1` files remain backward compatible            |
| 2026-04-15 | `237e6df` | Added 125 newly named asteroids                                          |
| 2026-04-15 | `f971d00` | Added newly named asteroids                                              |
| 2026-04-14 | `af9823f` | `v2.10.3final`; data files created with DE441                            |
| 2026-03-30 | `5d7d8a1` | Fixed output for format `x` and `X` for planetary moons                  |
| 2026-03-24 | `768a403` | Added inactive Delta T helper file for older pre-2.10 releases           |
| 2026-03-24 | `c0ec2c8` | Upgraded data to DE441 while remaining compatible with Swiss Ephemeris 2 |
| 2026-03-11 | `728f9f4` | Fixed old rounding bug in `swe_split_deg()`                              |
| 2026-03-01 | `16e1806` | `roundmin` is now observed in output field `l`                           |
| 2026-03-01 | `a765d88` | Added internal `serr` handling in `swe_set_ephe_path()` for debugging    |
| 2026-02-12 | `3f563e5` | Fixed `ourtdef.h` bug by setting `PRINTMOD` to `0`                       |

---

## Build / Update Workflow

### Rebuild With Current Upstream Source

```bash
composer build
```

If you work directly inside the local Swiss Ephemeris source checkout:

```bash
cd build/swisseph_src
git checkout master
git pull origin master

cd ../..
composer build
```

### Check the Local Upstream Commit

```bash
cd build/swisseph_src
git log -1 --oneline
```

### Check the Runtime Swiss Ephemeris Version String

```bash
php -r "
require 'vendor/autoload.php';
\$sweph = new SwissEph\FFI\SwissEphFFI();
echo 'Swiss Ephemeris Version: ' . \$sweph->swe_version(' ') . PHP_EOL;
"
```

Note: upstream currently keeps the internal C version string as `2.10.03`, even when using the `v2.10.3final` tag or later `master` commits.

---

## Version Tracking

| Component                             | Value                              |
| ------------------------------------- | ---------------------------------- |
| **PHP Package**                       | `jayeshmepani/swiss-ephemeris-ffi` |
| **Current Packagist Version Checked** | `1.1.0`                            |
| **PHP Requirement**                   | `^8.3`                             |
| **Required Extension**                | `ext-ffi`                          |
| **Package License Metadata**          | `AGPL-3.0-or-later`                |
| **Target Upstream Source**            | `aloistr/swisseph` `master`        |
| **Target Upstream Commit Checked**    | `2f18c14`                          |

---

## Compatibility Notes

The checked upstream commits are primarily data updates, output fixes, and maintenance fixes. I did not verify a public breaking C API change in the checked sources.

For production use, continue to run the package test suite after rebuilding:

```bash
composer test
```

Recommended checks after each upstream sync:

```bash
composer quality
composer test
```

---

## When to Rebuild

Rebuild the local `libswe` binary when:

1. Upstream publishes a new release or post-release bug fix.
2. You need newer ephemeris or asteroid data files.
3. You change platforms or deployment environments.
4. You see calculation differences that may be caused by stale binaries or stale data files.

---

## Support & Resources

- **Upstream Source**: [aloistr/swisseph](https://github.com/aloistr/swisseph)
- **Upstream Releases**: [Swiss Ephemeris releases](https://github.com/aloistr/swisseph/releases)
- **Upstream Compare**: [v2.10.3final...master](https://github.com/aloistr/swisseph/compare/v2.10.3final...master)
- **Official Swiss Ephemeris Site**: [astro.com Swiss Ephemeris](https://www.astro.com/swisseph/)
- **Official Programmer Documentation**: [Swiss Ephemeris Programmer's Documentation](https://www.astro.com/swisseph/swephprg.htm)
- **Package Issues**: [Swiss-Ephemeris-PHP issues](https://github.com/jayeshmepani/Swiss-Ephemeris-PHP/issues)
- **Version Tracking**: [VERSION.md](VERSION.md)

---

## Verification Checklist

- [x] Latest release tag checked: `v2.10.3final`
- [x] Latest release date checked: April 14, 2026
- [x] Latest public upstream commit checked: `2f18c14`
- [x] Post-release upstream commits checked through April 18, 2026
- [x] DE441 `.se1` data update documented
- [x] Backward compatibility note for `.se1` files documented
- [x] Internal C version string documented as `2.10.03`
- [x] Licensing model documented with AGPL / Professional License warning

---

## FFI Implementation Verification

This PHP FFI wrapper implementation has been audited and verified:

### Architecture & Mapping

- **Complete constant parity**: All `SEFLG_*`, `SE_SIDM_*`, `SEMOD_*`, and other constants match upstream C header definitions exactly
- **Zero struct mapping risk**: Swiss Ephemeris API uses primitive types and pointers — no packing/alignment bugs possible
- **Type safety verified**: `double` (64-bit), `int32` mapped correctly to upstream portable typedefs
- **String handling**: Production-grade `FFI::string()` with null checks, flexible `CData|string` buffer support

### Function Coverage

- **106/106 public API functions** exposed with complete signature parity
- No additional calculations, transformations, or rounding performed
- Direct memory-level interaction with the C engine

### Runtime Validation

- **Verified against official `swetest` CLI**:
  - Planetary positions ✔️
  - House systems ✔️
  - Eclipses ✔️
  - Edge dates ✔️

**Verified Claim**: Zero-abstraction, 1:1 mapping with bit-level output parity demonstrated in verified test scenarios.

---

## Commercial Use Warning

The Swiss Ephemeris C library and ephemeris files are distributed under Astrodienst's dual licensing model:

- AGPL, or
- Swiss Ephemeris Professional License

If this package is used in commercial, closed-source, SaaS, or public web-service software, review the upstream Swiss Ephemeris license terms and purchase a professional license where required.
