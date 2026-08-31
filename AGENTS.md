# AGENTS.md

Guidance for coding agents working in this repository. It is the single source
of truth for agent instructions; `CLAUDE.md` imports this file. The
human-facing counterpart is [CONTRIBUTING.md](CONTRIBUTING.md), which covers
the same ground plus the issue/branch/PR and release workflow.

## Project

`zlatanstajic/php-library` — a dependency-light PHP 8.5 utility library
published on Packagist. The only runtime dependency is
`phpoffice/phpspreadsheet` (used by [Import](src/core/files/Import.php) /
[Export](src/core/files/Export.php)). Documentation is built with Sphinx from
[docs/](docs/) and published to GitHub Pages.

## Commands

```bash
composer run check    # lint -> peck -> rector -> phpstan -> test; stops at first failure
composer run lint     # Laravel Pint, check only (src/ + tests/)
composer run fix      # Laravel Pint, apply fixes
composer run peck     # spell-checks comments and identifiers; needs aspell installed
composer run rector   # Rector dry-run over src/ + tests/
composer run rector:fix # apply Rector's proposed changes
composer run phpstan  # static analysis, level 7, src/ only
composer run test     # Pest + coverage reports; fails below 80% line coverage
composer run coverage # alias for composer run test
composer run hooks:install # points core.hooksPath at .githooks
```

All five `check` gates must be clean. Run the gate you touched first, then the
whole chain.

Run a single test file or filter:

```bash
vendor/bin/pest tests/Core/Data/RandomTest.php
vendor/bin/pest --filter "generates a numeric sequence"
```

The suite is self-contained: fixtures are generated at run time, and nothing
needs a database, a network or a downloaded folder. Live-resource tests are
skipped unless `PHPLIB_MYSQL=1` (`PDO_Connection`, `Dump`) or
`PHPLIB_NETWORK=1` (`Web_Service`) is set; the MySQL ones read
`PHPLIB_MYSQL_HOST`, `PHPLIB_MYSQL_USER`, `PHPLIB_MYSQL_PASS` and
`PHPLIB_MYSQL_NAME`, defaulting to `localhost`/`root` with an empty password.

`composer run test` writes an HTML report, a Clover XML report and a text
summary under `build/` (git-ignored). Coverage needs PCOV or Xdebug.

## Automation

* **Pre-commit hook.** [.githooks/pre-commit](.githooks/pre-commit) runs
  `composer check` before every commit. `composer install` and
  `composer update` install it via `composer run hooks:install`.
* **CI.** [.github/workflows/check.yml](.github/workflows/check.yml) runs
  `composer check` on PHP 8.5 and uploads the coverage report as an artifact.
  [.github/workflows/pages.yml](.github/workflows/pages.yml) builds the Sphinx
  docs with `--fail-on-warning` and deploys them to GitHub Pages — a docs
  warning fails the build, so keep cross-references and the `toctree` in
  [docs/index.md](docs/index.md) valid when adding a page.

## Architecture

### Autoloading

PSR-4 maps ten namespaces to **lowercase** directories in
[composer.json](composer.json) (e.g. `PHP_Library\Core\Arrangements\` →
`src/core/arrangements`). Class files are `Pascal_Snake_Case.php` matching the
class name. A new subdirectory under `src/` needs a new PSR-4 entry — there is
no root-level mapping. Prefer the subdirectory that already owns the concern
over opening a new one.

### Two layers

* `src/core/` — the public surface: `Arrangements`, `Data`, `Files`,
  `Numericals`, `Services`, `Sites`, `SQL`.
* `src/system/` — internal infrastructure the core classes inherit from:
  `Informations`, `Examinations`, `Associations`.

### Error handling is inheritance-based, not exception-based

**Nothing throws.** This is the library's defining contract.
[Message](src/system/informations/Message.php) accumulates three arrays
(`success`, `error`, `file`); setters are `protected`, getters (`get_error()`,
`has_errors()`, …) are `public`. Every stateful class extends it, directly or
through:

* [Testing](src/system/examinations/Testing.php) — adds `turn_on()` and
  `protected is_function_available()`. Calling `turn_on()` makes availability
  checks fail deliberately and then pops the message, so otherwise-unreachable
  failure branches can be driven from tests.
* [Connection](src/system/associations/Connection.php) — holds `$connection`
  plus a `host/user/pass/name` array defaulting to `localhost`/`root`.

Chains: `Dump`, `Sorter`, `Web_Service`, `Website` → `Testing` → `Message`;
`PDO_Connection` → `Connection` → `Message`.

A new class that can fail should extend `Message`/`Testing` and record via
`set_error()` — never throw, never return an error code.

Because failure paths record rather than throw, they legitimately emit PHP
warnings (a missing file, a bad config). `phpunit.xml` therefore fails on
**deprecations** but only surfaces warnings and notices.

### Static vs. instance classes

All-static, no state: `Format`, `Date_Time_Format`, `Email`, `Validation`,
`Random`, `Password`, `User_Agent`, `Math`, `Temperature`, `File`, `Import`,
`Export`, `Directory_Lister`. Instance-based with a constructor: `Dump`,
`Sorter`, `Web_Service`, `Website`, `PDO_Connection`. Instance constructors
take a single `$params` array, except `PDO_Connection` which takes four
positional strings.

## Tests

`tests/` mirrors `src/` with PascalCase directories and the underscores dropped
from the file name — `src/core/arrangements/Date_Time_Format.php` is covered by
`tests/Core/Arrangements/DateTimeFormatTest.php`. Shared fixture helpers
`temp_dir()`, `remove_dir()` and `write_png()` live in
[tests/Pest.php](tests/Pest.php); add new ones there instead of duplicating
setup. Assert the recorded error as well as the happy path — the failure branch
is the contract — and use `Testing::turn_on()` for branches that are otherwise
unreachable.

## Conventions

### No `declare(strict_types=1)`

Coercive mode is load-bearing: callers pass numeric strings
(`Temperature::c_to_f('20')`) and the code relies on `floatval()`/`intval()`
coercion. Parameters therefore use *widening* unions (`int|float|string`),
never narrowing ones. The Laravel Pint preset does not add
`declare_strict_types`, so nothing reintroduces it. `rector.php` uses
`withPhpSets()` only — it does not add a strict-types set either.

### Public statics are API

`Date_Time_Format::$types`, `File::$image` and `File::$errors` are public and
stay properties. Private config arrays became `private const`; do not promote
the public ones.

### Coding standard

**The default Laravel standard, via [Laravel Pint](https://laravel.com/docs/pint)'s
`laravel` preset.** There is deliberately no `pint.json` — the preset is
unmodified, so the standard is whatever Laravel ships. Do not hand-format: run
`composer run fix`.

What that means in practice, and what the preset does *not* touch:

* Braces follow PSR-12: on their own line for classes and methods, on the same
  line for control structures. (The previous Allman-everywhere style is gone.)
* `! $value` keeps its space after the operator — that is Laravel style, not a
  leftover.
* Imports are ordered alphabetically; `=` and `=>` are *not* aligned.
* Short arrays, lowercase `true`/`false`/`null`, single quotes where no
  interpolation is needed.
* `@package`, `@subpackage` and `@category` are stripped by the preset. File
  headers keep a summary and `@author` only.
* **Naming is not enforced by any tool.** `snake_case` methods,
  `Pascal_Snake_Case` classes and `UPPER_SNAKE` constants are the library's
  public API — Pint will never rename them, and neither should you.
* Docblocks carry `@param`/`@return` only where they add what a native type
  cannot (array shapes, `numeric-string`). Several of those narrowing tags are
  what keep PHPStan at level 7 — do not delete them as "redundant".

Earlier releases used a custom PHP_CodeSniffer ruleset (`phpcs.xml`); both the
file and the `squizlabs/php_codesniffer` dependency have been removed.

### Spelling

[Peck](https://github.com/peckphp/peck) spell-checks comments and identifiers
against `aspell`. When it flags a genuine technical term, add the lowercase
word to `ignore.words` in [peck.json](peck.json) — do not rename an identifier
to satisfy the checker.

## Behaviour quirks that are preserved deliberately

Do not "fix" these — callers depend on them, and each is commented at the site:

* `User_Agent::detect_browser()` / `detect_device()` use `strpos()` as a truthy
  test, so a signature at offset 0 reads as no match.
* `Random::generate(0, 'INT')` returns `null` while `generate(0, 'STRING')`
  returns `''`.
* `Validation::extension()` also requires the `$type` to be in
  `$allowed_types`, so the two-argument form always returns `false`.
* `Directory_Lister::listing()` with `method => 'files'` returns `false` when
  no top-level file matches.
* `Sorter` records a configuration error and still attempts the copies.

## Verifying a behaviour-preserving change

`src/` was modernised for PHP 8.5 under a 303-behaviour parity harness. If you
make another sweeping change, rebuild that safety net first: dump the public
surface to JSON before and after and diff, treating random and time-dependent
values by shape or digit-mask rather than by value.

## Known gaps

* `File::force_download()` ends in `readfile()` + `exit` behind a
  `headers_sent()` guard that is always false in CLI, so the emit path cannot
  be exercised in-process. Its input handling is tested; the emit path is not.
* `Website::image_size()` calls `getimagesize()` on whatever it is given,
  including remote URLs, so it can reach the network and warn on failure.
* PHPStan runs at level 7, and [phpstan.neon](phpstan.neon) ignores
  `missingType.iterableValue` — bare `array` types have always been accepted
  here. Level 8 reports 14 null-safety findings whose fixes would change
  failure-path behaviour (e.g. `curl_setopt()` when `curl_init()` failed).
