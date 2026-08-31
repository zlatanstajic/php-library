# PHP Library

[![Tests and coverage](https://github.com/zlatanstajic/php-library/actions/workflows/check.yml/badge.svg?branch=master)](https://github.com/zlatanstajic/php-library/actions/workflows/check.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE.md)
[![Coverage: 80%+](https://img.shields.io/badge/Coverage-80%25%2B-brightgreen.svg)](composer.json)
[![PHP 8.5](https://img.shields.io/badge/PHP-8.5-blue.svg)](https://www.php.net/)
[![Packagist](https://img.shields.io/badge/Packagist-zlatanstajic%2Fphp--library-orange.svg)](https://packagist.org/packages/zlatanstajic/php-library)

> Useful PHP building blocks, ready to compose.

A dependency-light set of PHP 8.5 classes whose attributes and methods facilitate the development of web applications. PHP Library covers dates, validation, files, spreadsheets, HTTP services, site metadata and SQL connections, and can be dropped into any project or framework — [CodeIgniter](https://www.codeigniter.com), [Laravel](https://laravel.com) or none at all.

<p align="center">
  <img src="assets/logos/logo-blue.png" alt="PHP Library" width="100%">
</p>

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Install](#install)
  - [Requirements](#requirements)
  - [Composer](#composer)
  - [Manual](#manual)
  - [From Source](#from-source)
- [Usage](#usage)
- [Library Reference](#library-reference)
  - [Core](#core)
  - [System](#system)
  - [Error Handling](#error-handling)
- [Documentation](#documentation)
- [Development](#development)
  - [Coding Standard](#coding-standard)
  - [Spelling](#spelling)
  - [Automated Refactoring](#automated-refactoring)
  - [Static Analysis](#static-analysis)
- [Testing](#testing)
  - [Live-Resource Tests](#live-resource-tests)
  - [Pre-commit Hook](#pre-commit-hook)
- [Contributing](#contributing)
- [License](#license)

---

## Features

- **Dependency-light:** One runtime dependency, [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io), and only for the spreadsheet import and export classes.
- **Dates and text:** Format dates between display and database shapes, normalize strings, and build email messages.
- **Data and validation:** Validate input, hash and verify passwords, generate random values, and parse user agents.
- **Files and spreadsheets:** Read and write files, list directories, sort files into folders, and import or export spreadsheets.
- **Numericals:** Arithmetic helpers and temperature conversion across Celsius, Fahrenheit and Kelvin.
- **Web and sites:** Call HTTP services over cURL and assemble site metadata such as titles, descriptions and social images.
- **Database:** PDO connections and MySQL dumps behind a small, uniform surface.
- **Nothing throws:** Failures are recorded on the object and read back with `has_errors()` and `get_error()`, so callers never wrap a call in `try`/`catch`.
- **Coercive by design:** Parameters take widening unions, so numeric strings from forms and CSV files are accepted as-is.
- **PSR-4 autoloading:** Ten namespaces mapped in [composer.json](composer.json); import a class and call it directly.

[⬆ back to top](#table-of-contents)

---

## Tech Stack

- **Language:** PHP 8.5
- **Runtime dependency:** [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io) 5.x
- **Testing:** [Pest](https://pestphp.com) with a required minimum coverage of 80%
- **Quality:** [Laravel Pint](https://laravel.com/docs/pint), [Peck](https://github.com/peckphp/peck), [Rector](https://getrector.com) and [PHPStan](https://github.com/phpstan/phpstan) at level 7
- **Documentation:** [Sphinx](https://www.sphinx-doc.org) with MyST, published to GitHub Pages
- **Distribution:** Composer and [Packagist](https://packagist.org/packages/zlatanstajic/php-library)

[⬆ back to top](#table-of-contents)

---

## Install

### Requirements

- PHP 8.5
- Composer 2
- PCOV or Xdebug, only to run the coverage-enforced test suite
- `aspell` and an English dictionary, only to run the spell checker

| PHP | Production | Development |
|---|---|---|
| 8.5 | Yes | Yes |

*Production* is the version the library runs on when installed into another project. *Development* is the version it is developed and tested on. Releases up to and including 1.x targeted PHP 7; this release requires PHP 8.5.

### Composer

Install the stable version into an existing project:

```bash
composer require zlatanstajic/php-library
```

### Manual

If you would rather not use Composer, download the [latest release](https://github.com/zlatanstajic/php-library/releases/latest) from the releases page.

### From Source

To develop the library itself, clone the repository and install its dependencies:

```bash
git clone https://github.com/zlatanstajic/php-library.git
cd php-library
composer install
```

`composer install` also installs the version-controlled pre-commit hook described under [Testing](#pre-commit-hook). The suite needs no further configuration: there is no `.env` file, no database and no downloaded folder, because every fixture is generated at run time.

[⬆ back to top](#table-of-contents)

---

## Usage

Composer provides the autoloader. Import the class you need and call it directly:

```php
<?php

require __DIR__.'/vendor/autoload.php';

use PHP_Library\Core\Arrangements\Date_Time_Format;
use PHP_Library\Core\Data\Validation;

$published = Date_Time_Format::format_to_database('29.08.2026');
```

Stateful classes are instantiated and report their outcome instead of throwing:

```php
use PHP_Library\Core\Services\Web_Service;

$service = new Web_Service('https://api.example.com/status');
$response = $service->response();

if ($service->has_errors()) {
    print_r($service->get_error());
}
```

[⬆ back to top](#table-of-contents)

---

## Library Reference

The library has two layers. `src/core/` is the public surface; `src/system/` is the internal infrastructure the core classes inherit from. All source files live under [src](src/), and every namespace is mapped in [composer.json](composer.json).

### Core

| Namespace | Classes | Purpose |
|---|---|---|
| `PHP_Library\Core\Arrangements` | `Date_Time_Format`, `Email`, `Format` | Date, text and email formatting |
| `PHP_Library\Core\Data` | `Password`, `Random`, `User_Agent`, `Validation` | Input validation, password hashing, random values and legacy user-agent hints |
| `PHP_Library\Core\Files` | `Directory_Lister`, `Export`, `File`, `Import`, `Sorter` | File handling, directory listing and spreadsheet import/export |
| `PHP_Library\Core\Numericals` | `Math`, `Temperature` | Arithmetic helpers and temperature conversion |
| `PHP_Library\Core\Services` | `Web_Service` | HTTP requests over cURL |
| `PHP_Library\Core\Sites` | `Website` | Site metadata such as titles, descriptions and images |
| `PHP_Library\Core\SQL` | `Dump`, `PDO_Connection` | PDO connections and MySQL dumps |

### System

| Namespace | Class | Purpose |
|---|---|---|
| `PHP_Library\System\Informations` | `Message` | Accumulates `success`, `error` and `file` messages |
| `PHP_Library\System\Examinations` | `Testing` | Adds availability checks that can be failed deliberately |
| `PHP_Library\System\Associations` | `Connection` | Holds a connection plus its host, user, password and name |

Classes are either all-static utilities — `Format`, `Date_Time_Format`, `Email`, `Validation`, `Random`, `Password`, `User_Agent`, `Math`, `Temperature`, `File`, `Import`, `Export`, `Directory_Lister` — or instance-based with a constructor: `Dump`, `Sorter`, `Web_Service`, `Website`, `PDO_Connection`. `Dump`, `Sorter` and `Website` are constructed with a single `$params` array; `Web_Service` takes a URL string and receives its options through `response()`; `PDO_Connection` takes four positional strings.

Legacy compatibility methods that retain global state, emit responses, or
duplicate native PHP APIs are documented in the
[migration guide](docs/migration.md). New code should use the composable
replacements listed there.

### Error Handling

**Nothing in this library throws.** [Message](src/system/informations/Message.php) accumulates three arrays behind `protected` setters and `public` getters, and every stateful class extends it — directly, or through [Testing](src/system/examinations/Testing.php) or [Connection](src/system/associations/Connection.php). Read the outcome back with `has_errors()`, `get_error()` and their `success` and `file` counterparts.

Because failure paths record rather than throw, they legitimately emit PHP warnings for a missing file or a bad configuration. [phpunit.xml](phpunit.xml) therefore fails on deprecations but only surfaces warnings and notices.

[⬆ back to top](#table-of-contents)

---

## Documentation

The published documentation lives at **<https://zlatanstajic.github.io/php-library/>** and covers installation, examples and the public API reference.

Sources are the MyST Markdown files under [docs](docs/), built with Sphinx. [`.github/workflows/pages.yml`](.github/workflows/pages.yml) rebuilds and publishes them on every push to `master`. To build them locally:

```bash
pip install -r docs/requirements.txt
make -C docs html
```

[⬆ back to top](#table-of-contents)

---

## Development

Run every quality gate in one go:

```bash
composer run check
```

That runs the coding standard, spell checker, Rector dry run, static analysis
and tests. The gates run in that order and stop at the first failure. Pull
requests and pushes to `master` run the same check in GitHub Actions.

### Coding Standard

PHP Library follows the default Laravel coding standard, applied with [Laravel Pint](https://laravel.com/docs/pint) using its `laravel` preset — the same style a fresh Laravel 13 application ships with. There is no `pint.json`: the preset is used unmodified, so the standard is whatever Laravel ships.

```bash
# Check the coding standard without changing anything
composer run lint

# Apply the coding standard
composer run fix
```

Naming is not enforced by Pint. The library's public `snake_case` methods, `Pascal_Snake_Case` classes and `UPPER_SNAKE` constants are deliberate and stay as they are.

Earlier releases used a custom [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer) ruleset inspired by CodeIgniter. That ruleset and its `phpcs.xml` file have been removed.

### Spelling

Comments, identifiers and file names are spell-checked with [Peck](https://github.com/peckphp/peck):

```bash
composer run peck
```

Peck shells out to `aspell`, so install it first — `sudo apt-get install aspell aspell-en` on Debian and Ubuntu, `brew install aspell` on macOS. Paths ignored by Git are skipped automatically.

Technical terms, PHP function names and the library's own British spellings are listed under `ignore.words` in [peck.json](peck.json). Add a term there rather than renaming a class, method or parameter to satisfy the checker — naming is public API.

### Automated Refactoring

[Rector](https://getrector.com) checks `src/` and `tests/` against the PHP
version declared in `composer.json`. Its configuration lives in
[rector.php](rector.php).

```bash
# Preview required refactors and fail when the tree is outdated
composer run rector

# Apply the configured refactors
composer run rector:fix
```

The dry run is part of `composer run check`, so the pre-commit hook and GitHub
Actions enforce the same Rector baseline.

### Static Analysis

[PHPStan](https://github.com/phpstan/phpstan) runs at level 7 over `src/`:

```bash
composer run phpstan
```

The configured rules are in [phpstan.neon](phpstan.neon).

[⬆ back to top](#table-of-contents)

---

## Testing

PHP Library is covered with [Pest](https://pestphp.com) tests. They generate every fixture they need at run time, so the suite is self-contained: no database, no network and no downloaded folder are required.

```bash
composer run test
```

Tests require PCOV or Xdebug. Every run enforces at least 80% line coverage and writes HTML, Clover XML and text reports under `build/`. `composer run coverage` is an alias for the same command.

During development, run the smallest relevant test first:

```bash
vendor/bin/pest tests/Core/Data/RandomTest.php

# Or filter by test name
vendor/bin/pest --filter "generates a numeric sequence"
```

The configured rules are in [phpunit.xml](phpunit.xml).

### Live-Resource Tests

A handful of tests exercise live resources and are skipped unless you opt in:

| Variable | Enables |
|---|---|
| `PHPLIB_MYSQL=1` | Live MySQL tests for `PDO_Connection` and `Dump` |
| `PHPLIB_NETWORK=1` | Live HTTP tests for `Web_Service` |

The MySQL tests read `PHPLIB_MYSQL_HOST`, `PHPLIB_MYSQL_USER`, `PHPLIB_MYSQL_PASS` and `PHPLIB_MYSQL_NAME`, defaulting to `localhost`/`root` with an empty password.

### Pre-commit Hook

The repository includes a version-controlled hook at [`.githooks/pre-commit`](.githooks/pre-commit) that runs `composer run check` before each commit. It is installed by Composer's `post-install-cmd` and `post-update-cmd` scripts. To install or refresh it explicitly, run:

```bash
composer run hooks:install
```

A failing check aborts the commit. Run `composer run check` directly to reproduce the failure. Bypass the hook for a single commit only when necessary with:

```bash
git commit --no-verify
```

[⬆ back to top](#table-of-contents)

---

## Contributing

Contributions are welcome. Open an issue to discuss a change, then submit a pull request that keeps `composer run check` green, including the 80% coverage requirement. See [CONTRIBUTING.md](CONTRIBUTING.md) for the branch naming rules, the layout conventions and the error-handling contract a new class must follow.

[⬆ back to top](#table-of-contents)

---

## License

This project is licensed under the MIT License. Copyright (c) Zlatan Stajic <contact@zlatanstajic.com>. See the [LICENSE](LICENSE.md) file for details.

[⬆ back to top](#table-of-contents)
