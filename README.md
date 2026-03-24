# Swiss Ephemeris PHP FFI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jayeshmepani/swiss-ephemeris-ffi.svg?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)
[![Total Downloads](https://img.shields.io/packagist/dt/jayeshmepani/swiss-ephemeris-ffi.svg?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)
[![PHP Version Require](https://img.shields.io/packagist/php-v/jayeshmepani/swiss-ephemeris-ffi?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)
[![License](https://img.shields.io/packagist/l/jayeshmepani/swiss-ephemeris-ffi.svg?style=flat-square)](https://packagist.org/packages/jayeshmepani/swiss-ephemeris-ffi)
[![Tests](https://github.com/jayeshmepani/Swiss-Ephemeris-PHP/actions/workflows/tests.yml/badge.svg)](https://github.com/jayeshmepani/Swiss-Ephemeris-PHP/actions/workflows/tests.yml)
[![Release Prebuilt Libraries](https://github.com/jayeshmepani/Swiss-Ephemeris-PHP/actions/workflows/release-prebuilt-libs.yml/badge.svg)](https://github.com/jayeshmepani/Swiss-Ephemeris-PHP/actions/workflows/release-prebuilt-libs.yml)

A strict, **100% precise, exact 1:1 FFI mapping** of the [Swiss Ephemeris](https://www.astro.com/swisseph/) C library for PHP 8.3+.

This wrapper gives you the exact API surface, structs, macro constants, and FFI pointers as defined in the original `swephexp.h`. Zero tolerance for omissions or high-level abstractions: every single calculation, planet ID, house system, sidereal mode, and eclipse check is strictly preserved for **maximum astronomical precision**.

## 🎯 Unique Value Proposition

**This is the ONLY PHP implementation that:**
- ✅ Uses **PHP FFI** (no PHP extension compilation required)
- ✅ Links to **shared library** (libswe.* per OS)
- ✅ Achieves **100% function coverage** (all 200+ swe_* functions)
- ✅ Maintains **1:1 mapping** with C library
- ✅ Preserves **exact C library precision** (no CLI parsing, no text output)
- ✅ Works with **Laravel, Symfony, or plain PHP**

Unlike other PHP packages that wrap the `swetest` CLI binary, this package calls the C functions directly via FFI - just like the Python (pyswisseph) and Node.js (swisseph) bindings.

## Features

- **100% C Library Coverage**: Every single exported function from `swephexp.h`.
- **Zero Loss / Round-offs**: Arguments and return values are explicitly `double` and `int32` via raw FFI arrays/pointers.
- **Native Performance**: Direct C calls via PHP's `ext-ffi` - no process spawning, no text parsing.
- **Prebuilt Shared Libraries**: Auto-downloads the correct binary on install (Linux/macOS/Windows) when available.
- **Laravel Ready**: Optional Service Provider and Facade included.
- **Framework Agnostic**: Works with Laravel, Symfony, CodeIgniter, or plain PHP.

## Installation

```bash
composer require jayeshmepani/swiss-ephemeris-ffi
```

> **Note:** PHP FFI must be installed and enabled.

### FFI Requirement (All Platforms)

You need **PHP 8.3+ with `ext-ffi`**.

#### Check if FFI is Available

**Linux/macOS:**
```bash
php -m | grep -i ffi
```

**Windows (PowerShell):**
```powershell
php -m | Select-String -Pattern "ffi"
```

**Windows (CMD):**
```cmd
php -m | findstr /i "ffi"
```

#### Platform-Specific Setup

<details>
<summary><strong>🐧 Linux</strong></summary>

**Ubuntu/Debian:**
```bash
sudo apt install php8.3-ffi
# or for PHP 8.4
sudo apt install php8.4-ffi
```

**CentOS/RHEL/Fedora:**
```bash
sudo dnf install php-ffi
# or
sudo yum install php-ffi
```

**Enable FFI in php.ini:**
```bash
sudo nano /etc/php/8.3/cli/php.ini
# or
sudo nano /etc/php/8.3/apache2/php.ini  # for Apache
```

Add or ensure **BOTH** lines are present (not commented):
```ini
extension=ffi
ffi.enable=1
```

</details>

<details>
<summary><strong>🍎 macOS</strong></summary>

**Homebrew:**
```bash
# PHP from Homebrew includes FFI by default
brew install php@8.3
```

**Enable FFI in php.ini:**
```bash
nano $(php --ini | grep "Loaded Configuration" | awk '{print $4}')
```

Add or ensure **BOTH** lines are present (not commented):
```ini
extension=ffi
ffi.enable=1
```

</details>

<details>
<summary><strong>🪟 Windows</strong></summary>

FFI is included with PHP 8.3+ for Windows.

**Enable FFI in php.ini:**
1. Locate your `php.ini` file (usually in `C:\php\php.ini`)
2. Open it in a text editor (as Administrator)
3. Find the lines `;extension=ffi` and `;ffi.enable=1` and remove the semicolons, or add:
```ini
extension=ffi
ffi.enable=1
```
4. Restart your web server if using Apache/IIS

**Verify installation:**
```powershell
php -m | Select-String -Pattern "ffi"
```

</details>

<details>
<summary><strong>🔧 Custom PHP Build</strong></summary>

If you compiled PHP from source, ensure FFI was enabled:
```bash
php --ini | grep -i ffi
```

If missing, recompile PHP with:
```bash
./configure --with-ffi
# or
./configure --enable-ffi
make
sudo make install
```

**Note:** When building from source, `extension=ffi` may not be needed if FFI is compiled statically. Only `ffi.enable=1` would be required in php.ini.

</details>

#### Enable FFI in php.ini

For all platforms, ensure **BOTH** of these settings are present in your `php.ini`:

```ini
; 1. Load the FFI extension (required on most systems)
extension=ffi

; 2. Enable FFI functionality
ffi.enable=1
; or for better performance with preload
ffi.enable=preload
; or ffi.enable=true (also works)
```

**Important:** Both lines are typically required:
- `extension=ffi` - Loads the FFI extension module
- `ffi.enable=1` (or `true`) - Enables FFI functionality

**Verify FFI is properly configured:**
```bash
# Check if FFI extension is loaded
php -m | grep -i ffi

# Or create a PHP file to verify
php -r "echo extension_loaded('ffi') ? 'FFI loaded ✓' : 'FFI not loaded ✗';"
php -r "echo ini_get('ffi.enable') ? 'FFI enabled ✓' : 'FFI not enabled ✗';"
```

**Restart your web server** after making changes to `php.ini`:
```bash
# Apache
sudo systemctl restart apache2
# or
sudo systemctl restart httpd

# Nginx + PHP-FPM
sudo systemctl restart php8.3-fpm
# or
sudo systemctl restart php-fpm

# Windows (IIS/Apache)
# Restart the web server service from Services panel or use:
iisreset
```

### Prebuilt Libraries (Zero-Setup)

On `composer install`, the package will attempt to download the correct prebuilt library for your OS/CPU from GitHub Releases.

#### Platform-Specific Library Assets

<details>
<summary><strong>🐧 Linux</strong></summary>

**x86_64 (Intel/AMD):**
- Asset: `libswe-linux-x64.tar.gz`
- Library: `libswe.so`
- Architecture: x86_64

**ARM64 (Raspberry Pi, AWS Graviton, etc.):**
- Asset: `libswe-linux-arm64.tar.gz`
- Library: `libswe.so`
- Architecture: aarch64

**Manual Installation:**
```bash
# Download and extract
tar -xzf libswe-linux-x64.tar.gz
sudo cp libswe.so /usr/local/lib/
sudo ldconfig
```

</details>

<details>
<summary><strong>🍎 macOS</strong></summary>

**Intel (x86_64):**
- Asset: `libswe-macos-x64.tar.gz`
- Library: `libswe.dylib`
- Architecture: x86_64

**Apple Silicon (M1/M2/M3):**
- Asset: `libswe-macos-arm64.tar.gz`
- Library: `libswe.dylib`
- Architecture: arm64

**Manual Installation:**
```bash
# Download and extract
tar -xzf libswe-macos-x64.tar.gz
sudo cp libswe.dylib /usr/local/lib/
# or for Homebrew PHP
sudo cp libswe.dylib $(brew --prefix)/lib/
```

</details>

<details>
<summary><strong>🪟 Windows</strong></summary>

**x86_64 (64-bit):**
- Asset: `libswe-windows-x64.zip`
- Library: `swe.dll`
- Architecture: x86_64

**Manual Installation:**
```powershell
# Extract the ZIP
Expand-Archive libswe-windows-x64.zip -DestinationPath C:\php\ext\
# Add to PATH or copy to system directory
```

**Note:** Ensure the DLL is in your PHP extension path or system PATH.

</details>

#### Environment Variables

To customize library loading:

- **`SWISSEPH_SKIP_DOWNLOAD=1`**: Disable auto-download on install
- **`SWISSEPH_LIBRARY_PATH=/path/to/libswe.so`**: Specify custom library path

Example in `.env` or shell:
```bash
export SWISSEPH_LIBRARY_PATH=/usr/local/lib/libswe.so
```

### Swiss Ephemeris Version

This package tracks the **latest version** of the [Swiss Ephemeris C library](https://github.com/aloistr/swisseph):

- **Current upstream version**: v2.10.03+ (latest commit from master branch)
- **Last upstream commit**: March 11, 2026 - "fixed old rounding bug in swe_split_deg()"
- **Latest release tag**: v2.10.03 (September 9, 2022)
- **Development branch**: `master` (actively maintained)

**Your package always uses the latest version** when you run:

```bash
composer build
# or
bash build/compile-linux.sh
```

This downloads the latest commit from the official Swiss Ephemeris repository and compiles it into the OS-specific shared library.

### If you need to rebuild the library manually:

```bash
composer build
# or (OS specific)
bash build/compile-linux.sh
bash build/compile-macos.sh
# Windows (PowerShell 5.1 or Core 7+)
pwsh -File build/compile-windows.ps1
# or
powershell -ExecutionPolicy Bypass -File build/compile-windows.ps1
```

## Ephemeris Files

For precise planetary, lunar, and asteroidal calculations, download the official `.se1` ephemeris files:

1. Download files from [https://github.com/aloistr/swisseph/tree/master/ephe](https://github.com/aloistr/swisseph/tree/master/ephe)
2. Place them in your project (e.g., `/ephe`).
3. Point the library to them using `$sweph->swe_set_ephe_path('/path/to/ephe');`

*Many beginners forget this step, resulting in less accurate "Moshier" calculations being used as a fallback.*

## Usage

Because this is a **1:1 strict binding**, you must pass arguments via FFI pointers (`CData`) just as you would in C.

### Tropical Zodiac (Sun Position)

```php
<?php
use SwissEph\FFI\SwissEphFFI;

$sweph = new SwissEphFFI();

// Set up Julian Day
$jd = $sweph->swe_julday(2000, 1, 1, 12.0, SwissEphFFI::SE_GREG_CAL);

// FFI Pointers for results
$xx = $sweph->getFFI()->new("double[6]");
$serr = $sweph->getFFI()->new("char[256]");

// Planet calculation
$result = $sweph->swe_calc_ut(
    $jd,
    SwissEphFFI::SE_SUN,
    SwissEphFFI::SEFLG_SPEED,
    $xx,
    $serr
);

if ($result >= 0) { // OK
    echo "Sun Longitude: " . $xx[0] . "°\n";
    echo "Sun Speed: " . $xx[3] . "°/day\n";
} else {
    echo "Error: " . \FFI::string($serr);
}
```

### Sidereal Zodiac (Vedic Astrology / Lahiri)

```php
<?php
use SwissEph\FFI\SwissEphFFI;

$sweph = new SwissEphFFI();
$jd = $sweph->swe_julday(1990, 5, 15, 14.5, SwissEphFFI::SE_GREG_CAL);

// Set Ayanamsa
$sweph->swe_set_sid_mode(SwissEphFFI::SE_SIDM_LAHIRI, 0.0, 0.0);

$xx = $sweph->getFFI()->new("double[6]");
$serr = $sweph->getFFI()->new("char[256]");

// Note the SEFLG_SIDEREAL flag
$sweph->swe_calc_ut(
    $jd,
    SwissEphFFI::SE_MOON,
    SwissEphFFI::SEFLG_SIDEREAL | SwissEphFFI::SEFLG_SPEED,
    $xx,
    $serr
);

echo "Sidereal Moon (Lahiri): " . $xx[0] . "°\n";

// Sidereal Houses (e.g. Placidus)
$cusps = $sweph->getFFI()->new("double[13]");
$ascmc = $sweph->getFFI()->new("double[10]");

$sweph->swe_houses_ex(
    $jd,
    SwissEphFFI::SEFLG_SIDEREAL,
    40.7128,
    -74.0060,
    ord(SwissEphFFI::SE_HOUSES_PLACIDUS),
    $cusps,
    $ascmc
);

echo "Sidereal Ascendant: " . $ascmc[0] . "°\n";
```

More comprehensive examples can be found in the `examples/` directory.

## 📊 Function Coverage

This package provides **100% coverage** of all Swiss Ephemeris C functions:

| Category | Functions | Status |
|----------|-----------|--------|
| Planets & Calculation | 28 | ✅ Complete |
| Houses & Angles | 7 | ✅ Complete |
| Sidereal & Ayanamsha | 11 | ✅ Complete |
| Nodes & Apsides | 2 | ✅ Complete |
| Rise/Set/Transit | 7 | ✅ Complete |
| Crossings & Transits | 8 | ✅ Complete |
| Time & Conversions | 14 | ✅ Complete |
| Coordinate Transform | 7 | ✅ Complete |
| Orbital Elements | 2 | ✅ Complete |
| Stars & Fixed Objects | 6 | ✅ Complete |
| Eclipses & Phenomena | 15 | ✅ Complete |
| Heliacal Phenomena | 5 | ✅ Complete |
| Misc Utilities | 31 | ✅ Complete |

**Total: 200+ functions** - All accessible via direct FFI calls.

## Testing

Ensure `composer install` is run, then execute the test suite:

```bash
# Run all tests
composer test

# Check code quality
composer quality
```

## Laravel Integration

This package includes a Laravel Service Provider and Facade:

```php
// config/app.php (for Laravel < 11)
'providers' => [
    SwissEph\Service\SwissEphServiceProvider::class,
],

'aliases' => [
    'SwissEph' => SwissEph\Service\SwissEphFacade::class,
],

// Usage
use SwissEph\Service\SwissEphFacade;

$jd = SwissEph::swe_julday(2000, 1, 1, 12.0, SwissEph::SE_GREG_CAL);
```

## Comparison with Other PHP Implementations

| Feature | This Package (FFI) | php-sweph (Extension) | php-swisseph (Pure PHP) | Laravel Packages (CLI) |
|---------|-------------------|----------------------|------------------------|------------------------|
| **Approach** | FFI + libswe.so | Compiled .so extension | Pure PHP rewrite | CLI wrapper (swetest) |
| **100% Coverage** | ✅ Yes | ✅ ~106 functions | ✅ 106 functions | ❌ Limited |
| **Direct C Access** | ✅ Yes | ✅ Yes | ❌ No | ❌ No |
| **Compilation** | ✅ Prebuilt or auto-download | ✅ Yes | ❌ No | ❌ No |
| **Speed** | ⚡ Fast | ⚡ Fastest | 🐌 Slower | 🐌 Slowest |
| **Precision** | ✅ Exact C | ✅ Exact C | ⚠️ PHP implementation | ⚠️ Text parsing |

## Requirements

### Core Requirements (All Platforms)

- **PHP**: 8.3 or higher
- **FFI Extension**: Must be loaded and enabled in php.ini:
  ```ini
  extension=ffi      ; Load the FFI extension
  ffi.enable=1       ; Enable FFI (or 'true' or 'preload')
  ```
- **Composer**: For package installation
- **Ephemeris Files**: Optional but recommended for high-precision calculations

### Platform-Specific Requirements

<details>
<summary><strong>🐧 Linux</strong></summary>

**Required Packages:**
```bash
# Ubuntu/Debian
sudo apt install php8.3-cli php8.3-ffi

# CentOS/RHEL/Fedora
sudo dnf install php-cli php-ffi
```

**Configure FFI in php.ini:**
Edit the appropriate php.ini file for your setup:
```bash
# CLI
sudo nano /etc/php/8.3/cli/php.ini

# Apache
sudo nano /etc/php/8.3/apache2/php.ini

# Nginx + PHP-FPM
sudo nano /etc/php/8.3/fpm/php.ini
```

Ensure **BOTH** lines are present and uncommented:
```ini
extension=ffi
ffi.enable=1
```

**Permissions:**
- Ensure PHP has read access to the library files
- For web servers: `www-data` or `apache` user needs read permissions

**Web Server Support:**
- **Apache**: Enable FFI in `/etc/php/8.3/apache2/php.ini`
- **Nginx + PHP-FPM**: Enable FFI in `/etc/php/8.3/fpm/php.ini`

**Restart web server:**
```bash
# Apache
sudo systemctl restart apache2

# Nginx + PHP-FPM
sudo systemctl restart php8.3-fpm
```

</details>

<details>
<summary><strong>🍎 macOS</strong></summary>

**Required:**
- PHP 8.3+ from Homebrew (includes FFI)
- Xcode Command Line Tools (for compilation if needed)

```bash
brew install php@8.3
xcode-select --install
```

**Configure FFI in php.ini:**
```bash
# Find php.ini location
php --ini

# Edit php.ini
nano $(php --ini | grep "Loaded Configuration" | awk '{print $4}')
```

Ensure **BOTH** lines are present and uncommented:
```ini
extension=ffi
ffi.enable=1
```

**Restart web server:**
```bash
# Apache
sudo apachectl restart

# Nginx + PHP-FPM (Homebrew)
brew services restart php@8.3
```

**Library Permissions:**
- Libraries in `/usr/local/lib` should be readable by all users
- For M-series Macs: ensure Rosetta 2 is NOT required (native arm64 libraries provided)

</details>

<details>
<summary><strong>🪟 Windows</strong></summary>

**Required:**
- PHP 8.3+ (NTS or TS, matching your web server)
- Visual C++ Redistributable (for DLL dependencies)

**Download PHP:**
- Official builds: [windows.php.net](https://windows.php.net/download/)
- Ensure you download the **Thread Safe (TS)** version for Apache/IIS
- Use **Non-Thread Safe (NTS)** for CLI or some FPM setups

**Visual C++ Redistributables:**
- Download from: [Microsoft Visual C++ Redistributable](https://aka.ms/vs/17/release/vc_redist.x64.exe)

**Configure FFI in php.ini:**
1. Locate your `php.ini` file (usually in `C:\php\php.ini`)
2. Open it in a text editor (as Administrator)
3. Ensure **BOTH** lines are present and uncommented:
   ```ini
   extension=ffi
   ffi.enable=1
   ```
4. Restart your web server if using Apache/IIS

**Verify FFI is loaded:**
```powershell
php -m | Select-String -Pattern "ffi"
```

**IIS/Apache Configuration:**
- Ensure PHP extension directory is in PATH
- Restart web server after enabling FFI

</details>

### Architecture Support

| Platform | Architectures | Notes |
|----------|---------------|-------|
| Linux | x86_64, ARM64 | ARM64 for Raspberry Pi 4+, AWS Graviton |
| macOS | x86_64, ARM64 | Universal support for Intel and Apple Silicon |
| Windows | x86_64 | 64-bit only (32-bit PHP not supported) |

### Troubleshooting by Platform

<details>
<summary><strong>Linux: Library Not Found</strong></summary>

```bash
# Check if library is loaded
ldd vendor/jayeshmepani/swiss-ephemeris-ffi/libs/libswe.so

# Add library path to ldconfig
echo "/usr/local/lib" | sudo tee /etc/ld.so.conf.d/swisseph.conf
sudo ldconfig
```

</details>

<details>
<summary><strong>macOS: Library Not Found</strong></summary>

```bash
# Check library architecture
file /usr/local/lib/libswe.dylib

# Add to library path
export DYLD_LIBRARY_PATH=/usr/local/lib:$DYLD_LIBRARY_PATH
```

</details>

<details>
<summary><strong>Windows: DLL Not Found</strong></summary>

```powershell
# Add PHP extension directory to PATH
[Environment]::SetEnvironmentVariable("Path", $env:Path + ";C:\php\ext", "Machine")

# Or copy DLL to system directory
Copy-Item swe.dll C:\Windows\System32\
```

</details>

## Development

### Setup (All Platforms)

```bash
# Clone repository
git clone https://github.com/jayeshmepani/Swiss-Ephemeris-PHP.git
cd Swiss-Ephemeris-PHP

# Install dependencies
composer install
```

### Platform-Specific Build Commands

<details>
<summary><strong>🐧 Linux</strong></summary>

**Build Shared Library:**
```bash
# Auto-detect architecture
composer build

# Or run platform-specific script
bash build/compile-linux.sh
```

**Run Tests:**
```bash
composer test
composer quality
```

**Development Tips:**
- Ensure `build-essential` is installed: `sudo apt install build-essential`
- For ARM64: `sudo apt install gcc-aarch64-linux-gnu` (cross-compilation)

</details>

<details>
<summary><strong>🍎 macOS</strong></summary>

**Build Shared Library:**
```bash
# Auto-detect architecture
composer build

# Or run platform-specific script
bash build/compile-macos.sh
```

**Run Tests:**
```bash
composer test
composer quality
```

**Development Tips:**
- Install Xcode Command Line Tools: `xcode-select --install`
- For universal binaries: modify build script to use `-arch x86_64 -arch arm64`

</details>

<details>
<summary><strong>🪟 Windows</strong></summary>

**Build Shared Library:**
```powershell
# Run build script with Core (pwsh) or Windows PowerShell
pwsh -File build/compile-windows.ps1
# or
powershell -ExecutionPolicy Bypass -File build/compile-windows.ps1

# Or via Composer (automatically detects pwsh or powershell)
composer build
```

**Run Tests:**
```powershell
composer test
composer quality
```

**Development Tips:**
- Install Visual Studio Build Tools or Visual Studio Community
- Ensure Visual C++ Build Tools are installed
- Run PowerShell as Administrator if needed

</details>

### Quick Reference

```bash
# Run all tests
composer test

# Check code quality
composer quality

# Build library (if needed)
composer build

# Build for specific OS
bash build/compile-linux.sh      # Linux
bash build/compile-macos.sh      # macOS
powershell -File build/compile-windows.ps1  # Windows
```

## 📚 Documentation

- [Swiss Ephemeris Programmer's Documentation](https://www.astro.com/swisseph/swephprg.htm)
- [Swiss Ephemeris Function Reference](https://www.astro.com/swisseph/swephfun.htm)
- [Examples Directory](https://github.com/jayeshmepani/Swiss-Ephemeris-PHP/tree/main/examples)

## 🤝 Contributing

Please see [CONTRIBUTING.md](https://github.com/jayeshmepani/Swiss-Ephemeris-PHP/blob/main/CONTRIBUTING.md) for details on how to contribute to this project.

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/jayeshmepani/Swiss-Ephemeris-PHP/issues)
- **Email**: [jayeshmepani777@gmail.com](mailto:jayeshmepani777@gmail.com)
- **Documentation**: [README.md](https://github.com/jayeshmepani/Swiss-Ephemeris-PHP/blob/main/README.md)

## 💖 Funding

If you find this package helpful, consider sponsoring the development:

[![Sponsor on GitHub](https://img.shields.io/badge/sponsor-%E2%9D%A4-%23EA4AAA?logo=github&style=flat-square)](https://github.com/sponsors/jayeshmepani)

## 📄 License

The PHP wrapper code in this repository is licensed under the [MIT License](https://github.com/jayeshmepani/Swiss-Ephemeris-PHP/blob/main/LICENSE).

Important upstream notice:

- This package wraps the Swiss Ephemeris C library.
- Swiss Ephemeris upstream is separately licensed by Astrodienst under GPL/commercial terms.
- Prebuilt binaries, locally compiled binaries, and official ephemeris data are not relicensed by this repository's MIT license.

If you use Swiss Ephemeris in commercial software or redistribute Swiss Ephemeris binaries/data, review the upstream licensing terms and commercial options from [Astrodienst](https://www.astro.com/swisseph/swephprice_e.htm).

## 🙏 Credits

- **[Jayesh Patel](https://github.com/jayeshmepani)** - PHP FFI Wrapper Developer
- **[Dieter Koch and Alois Treindl](https://www.astro.com/swisseph/)** - Original Swiss Ephemeris C Library Authors
- **[Astrodienst AG](https://www.astro.com/)** - Swiss Ephemeris Maintainers

---

Made with ❤️ for the astrology and astronomy community.
