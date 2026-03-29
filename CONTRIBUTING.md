# Contributing to Swiss Ephemeris PHP FFI

Thank you for considering contributing to Swiss Ephemeris PHP FFI! I appreciate your interest in making my package better.

## Code of Conduct

This project adheres to the [Laravel Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct). By participating, you are expected to uphold this code.

## How to Contribute

### Reporting Bugs

Before creating bug reports, please check the existing issues as you might find out that you don't need to create one. When you are creating a bug report, please include as many details as possible:

* **Use a clear and descriptive title**
* **Describe the exact steps to reproduce the problem**
* **Provide specific examples to demonstrate the steps**
* **Describe the behavior you observed and what behavior you expected**
* **Include error messages and stack traces if applicable**
* **Include your PHP version, OS, and Swiss Ephemeris version**

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, please include:

* **Use a clear and descriptive title**
* **Provide a detailed description of the suggested enhancement**
* **Explain why this enhancement would be useful**
* **List some examples of how this enhancement would be used**

### Pull Requests

1. **Fork the repository**
2. **Create a new branch** from `main` with a descriptive name (e.g., `feature/add-house-calculations`, `fix/ffi-pointer-issue`)
3. **Make your changes** following my coding standards
4. **Write or update tests** to cover your changes
5. **Ensure all tests pass** by running `composer test`
6. **Run code style checks** with `composer lint`
7. **Update documentation** if necessary
8. **Submit a pull request** with a clear description of your changes

## Development Setup

### Requirements

* PHP 8.3 or higher with FFI extension enabled
* Composer
* Git
* C compiler (gcc) for building libswe.so (optional, pre-built binaries included)

### Installation

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/Swiss-Ephemeris-PHP.git
cd Swiss-Ephemeris-PHP

# Install dependencies
composer install

# Run tests
composer test

# Run code style checks
composer lint
```

### FFI Configuration

Ensure FFI is enabled in your `php.ini`:

```ini
ffi.enable=preload
# or
ffi.enable=true
```

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test:coverage

# Run specific test file
vendor/bin/phpunit tests/SpecificTest.php
```

### Code Style

We use [Laravel Pint](https://github.com/laravel/pint) for code formatting:

```bash
# Fix code style issues
composer lint

# Check code style without fixing
composer lint:check
```

### Static Analysis

We use [PHPStan](https://phpstan.org/) for static analysis:

```bash
# Run PHPStan
composer phpstan
```

## Coding Standards

* Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
* Use meaningful variable and method names
* Add PHPDoc comments for public methods
* Keep methods small and focused
* Write tests for new features
* Maintain 1:1 compatibility with Swiss Ephemeris C library

## Important Notes

### 1:1 C Library Compatibility

This package aims for **100% 1:1 compatibility** with the Swiss Ephemeris C library. When contributing:

* Do NOT change function signatures
* Do NOT add high-level abstractions to core FFI classes
* Do NOT remove or rename constants
* DO maintain exact C data types (double, int32, etc.)
* DO test against original C library behavior

### Swiss Ephemeris Licensing

This repository is licensed under **AGPL-3.0-or-later**.

> **⚠️ Commercial Use Warning**
> 
> If you use my package in **SaaS/web applications**, you must either:
> - Make your source code available under AGPL-3.0, OR
> - Purchase a commercial license from [Astrodienst](https://www.astro.com/swisseph/swephprice_e.htm)
> 
> The Swiss Ephemeris C library upstream also has **AGPL/commercial licensing** - see [Astrodienst](https://www.astro.com/swisseph/swephprice_e.htm) for details.

Contributions to my repository are accepted under AGPL-3.0, while use or redistribution of upstream Swiss Ephemeris binaries/data must respect the upstream license.

## Release Process

Releases follow [Semantic Versioning](https://semver.org/):

* **MAJOR** version for incompatible changes or Swiss Ephemeris major version updates
* **MINOR** version for backwards-compatible features
* **PATCH** version for backwards-compatible bug fixes

## Questions?

Feel free to open an issue for any questions or concerns.

Thank you for contributing! 🚀
