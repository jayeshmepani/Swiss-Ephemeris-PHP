# Swiss Ephemeris Version Tracking

This document tracks the Swiss Ephemeris C library source used by this PHP FFI wrapper.

**Last verified**: April 25, 2026

---

## Current Version

| Attribute                        | Value                                                   |
| -------------------------------- | ------------------------------------------------------- |
| **Upstream Repository**          | [aloistr/swisseph](https://github.com/aloistr/swisseph) |
| **Latest Release Tag**           | `v2.10.3final`                                          |
| **Latest Release Date**          | April 14, 2026                                          |
| **Release Commit**               | `af9823f`                                               |
| **Development Branch Checked**   | `master`                                                |
| **Latest Public Commit Checked** | `2f18c14`                                               |
| **Latest Public Commit Date**    | April 18, 2026                                          |
| **Latest Commit Message**        | `fixed bug in semo4200.se1`                             |
| **Internal C Version String**    | `2.10.03`                                               |

---

## FFI Technical Verification

This PHP FFI wrapper has been rigorously audited for correctness:

| Area                   | Status                                                                            |
| ---------------------- | --------------------------------------------------------------------------------- |
| **Constant Parity**    | All `SEFLG_*`, `SE_SIDM_*`, `SEMOD_*` constants present and match C definitions   |
| **Struct Mapping**     | Not required — Swiss Ephemeris API uses primitive pointer-based interface         |
| **Type Alignment**     | Verified correct — `double` (64-bit), `int32` mapped correctly                    |
| **String Handling**    | Production-grade `FFI::string()` with null checks, `CData\|string` buffer support |
| **Function Coverage**  | 106/106 public API functions exposed                                              |
| **Runtime Validation** | Verified against `swetest` CLI for planets, houses, eclipses, edge dates          |

**Verified Claim**: This wrapper provides a zero-abstraction, 1:1 mapping with complete constant and signature parity. No additional calculations, transformations, or rounding are performed. Output parity demonstrated in verified test scenarios.

---

## Version History

### April 2026 Update

- **Base release**: `v2.10.3final` released on April 14, 2026.
- **Post-release master state checked**: `2f18c14` from April 18, 2026.
- **Release note**: `v2.10.3final` is described upstream as the final release of version 2.10.3 before publication of Swiss Ephemeris 3.0.
- **DE441 integration**: upstream states that all `.se1` data files for planets and asteroids were rebuilt with JPL Ephemeris DE441 as of April 14, 2026.
- **Backward compatibility**: upstream states that the rebuilt `.se1` files remain compatible with older Swiss Ephemeris versions at least back to release 1.67.
- **Asteroid data**:
  - upstream documents more than 760,000 numbered asteroids;
  - upstream documents more than 25,000 named asteroids;
  - commit history includes asteroid list updates through numbered asteroid `793066`.
- **Post-release fixes after `v2.10.3final`**:
  - fixed bug in `semo4200.se1`;
  - added 125 newly named asteroids;
  - added DE441 backward-compatibility note.
- **Important logic/output fixes included since `v2.10.03`**:
  - fixed old rounding bug in `swe_split_deg()`;
  - `roundmin` is now observed in output field `l`;
  - fixed output formatting for planetary moons with format `x` and `X`;
  - added inactive Delta T helper file for older pre-2.10 releases;
  - fixed `ourtdef.h` by setting `PRINTMOD` to `0`.

### v2.10.03

| Attribute        | Value             |
| ---------------- | ----------------- |
| **Release Date** | September 9, 2022 |
| **Tag**          | `v2.10.03`        |
| **Commit**       | `175e1fc`         |

The `v2.10.03` release itself fixed missing lunar eclipse detections in `swe_lun_eclipse_when()` for years 766-987 CE and improved the Moon magnitude model in `swe_pheno()` near the Sun.

---

## Compare Summary

| Compare                   | Result                         |
| ------------------------- | ------------------------------ |
| `v2.10.03...v2.10.3final` | 204 commits, 345 files changed |
| `v2.10.3final...master`   | 6 commits, 154 files changed   |

GitHub may not fully render these large diffs in the browser. For a complete local check, run:

```bash
git diff v2.10.03...v2.10.3final
git diff v2.10.3final...master
```

---

## Outlook: Swiss Ephemeris 3.0

Swiss Ephemeris 3.0 is expected as the next major source-code release after the 2.10.3 final release.

At the time of this check:

- no public `v3.0` release tag was found;
- no public `v3.0` changelog was found;
- no public breaking API list was found.

This package should be reviewed and tested again when Swiss Ephemeris 3.0 is published. A package major-version bump may be appropriate if upstream introduces breaking API or data-handling changes.

---

## How to Update

This package is intended to use the latest upstream `master` source when rebuilding the native library.

```bash
composer build
```

Check the local upstream commit:

```bash
cd build/swisseph_src
git log -1 --oneline
```

Check the runtime Swiss Ephemeris version string:

```bash
php -r "
require 'vendor/autoload.php';
\$sweph = new SwissEph\FFI\SwissEphFFI();
echo \$sweph->swe_version(' ') . PHP_EOL;
"
```

Note: the runtime version string may still print `2.10.03` because the upstream C header still defines `SE_VERSION` as `2.10.03`.

---

## Upstream Repository Links

- **GitHub**: [aloistr/swisseph](https://github.com/aloistr/swisseph)
- **Releases**: [Swiss Ephemeris releases](https://github.com/aloistr/swisseph/releases)
- **Commits**: [master branch commits](https://github.com/aloistr/swisseph/commits/master)
- **Compare v2.10.03 to v2.10.3final**: [v2.10.03...v2.10.3final](https://github.com/aloistr/swisseph/compare/v2.10.03...v2.10.3final)
- **Compare v2.10.3final to master**: [v2.10.3final...master](https://github.com/aloistr/swisseph/compare/v2.10.3final...master)
- **Official Site**: [astro.com Swiss Ephemeris](https://www.astro.com/swisseph/)

---

## License Compatibility

This PHP package metadata declares **AGPL-3.0-or-later**.

The upstream Swiss Ephemeris C library and related ephemeris files remain subject to the upstream Swiss Ephemeris licensing model: AGPL or Swiss Ephemeris Professional License.

If you use Swiss Ephemeris in commercial software, closed-source software, SaaS, or a public web application, review Astrodienst's commercial licensing terms before use.
