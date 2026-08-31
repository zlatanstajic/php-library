# Contributing

Contributions are welcome. To propose a change:

1. **Open an issue, then branch.** Every change starts from a GitHub issue — open one first, whatever the size. Fork the repository, then create a branch off `master` under the `issues/` prefix: it is the only accepted prefix, and an `issues/` branch is the only thing you may push. Branch names are **kebab-case only** — lowercase ASCII letters, digits, and single hyphens as separators, matching `^issues/[a-z0-9]+(-[a-z0-9]+)*$`. No underscores, dots, spaces, slashes beyond the prefix, or capitals. Lead with the issue number so the branch is traceable (e.g. `issues/42-mysql-dump-flags`).
2. **Set up the tooling.** The library targets **PHP 8.5** and requires PCOV or Xdebug to run its coverage-enforced test suite; Composer installs the project dependencies. Nothing else needs to be configured and there is no `.env` file — the suite generates every fixture it needs at run time, so no database, no network and no downloaded folder are required.

   ```bash
   git clone https://github.com/zlatanstajic/php-library.git
   cd php-library
   composer install
   ```

3. **Work within the existing structure.** The library has two layers. `src/core/` is the public surface — `Arrangements`, `Data`, `Files`, `Numericals`, `Services`, `Sites`, `SQL`. `src/system/` is the internal infrastructure the core classes inherit from — `Informations`, `Examinations`, `Associations`. Put a new class in the subdirectory that already owns its concern rather than opening a new one.

   Autoloading is PSR-4 with **Pascal_Snake_Case namespaces mapped to lowercase directories** (`PHP_Library\Core\Arrangements\` → `src/core/arrangements`). Class files are named for the class (`Date_Time_Format.php`). There is no root-level mapping, so a genuinely new subdirectory under `src/` also needs a new entry in [composer.json](composer.json).

   `tests/` mirrors `src/` with PascalCase directories and the underscores dropped from the file name — `src/core/arrangements/Date_Time_Format.php` is covered by `tests/Core/Arrangements/DateTimeFormatTest.php`. Shared fixture helpers (`temp_dir()`, `remove_dir()`, `write_png()`) live in [tests/Pest.php](tests/Pest.php); add new ones there instead of duplicating setup across files.
4. **Match conventions.** PHP Library uses the default Laravel Pint `laravel` preset over both `src/` and `tests/`. Run `composer run fix` to apply it. Naming is not enforced by Pint: retain the library's public `snake_case` methods, `Pascal_Snake_Case` classes and `UPPER_SNAKE` constants. Comments and identifiers are spell-checked by [Peck](https://github.com/peckphp/peck); add a genuine technical term to the `ignore.words` list in [peck.json](peck.json) rather than reworking a name to satisfy the checker.

   **Do not add `declare(strict_types=1)`.** Coercive mode is load-bearing: callers pass numeric strings (`Temperature::c_to_f('20')`) and the code relies on `floatval()`/`intval()` coercion. Parameters therefore take *widening* unions (`int|float|string`). Neither the Laravel Pint preset nor [rector.php](rector.php) adds a strict-types rule, so nothing reintroduces it.
5. **Respect the error-handling contract.** **Nothing in this library throws.** [Message](src/system/informations/Message.php) accumulates `success`, `error` and `file` arrays behind `protected` setters and `public` getters (`get_error()`, `has_errors()`, …), and every stateful class extends it — directly, or through [Testing](src/system/examinations/Testing.php) or [Connection](src/system/associations/Connection.php). A new class that can fail extends `Message`/`Testing` and records via `set_error()`; it never throws and never returns an error code.

   Because failure paths record rather than throw, they legitimately emit PHP warnings (a missing file, a bad configuration). [phpunit.xml](phpunit.xml) therefore fails on **deprecations** — that is what keeps the suite honest about PHP 8.5 — but only surfaces warnings and notices.
6. **Cover the change with tests.** The suite uses [Pest](https://pestphp.com). Assert the recorded error as well as the happy path — the failure branch is the contract. Fixtures are generated at run time; the library ships no binary test data, so build what you need with the `tests/Pest.php` helpers and clean it up. `Testing::turn_on()` exists to drive otherwise-unreachable availability failures, and is the way to test a branch you cannot reach for real.
7. **Validate before submitting.** Check the file you changed first, then run the complete suite:

   ```bash
   vendor/bin/pest tests/Core/Data/RandomTest.php
   vendor/bin/pest --filter "generates a numeric sequence"

   composer run check
   ```

   `composer run check` runs Pint in check-only mode, the spell checker, Rector, static analysis and coverage-enforced tests in that order, and stops at the first failure. The individual gates are `composer run lint`, `composer run peck` (spelling, configured in [peck.json](peck.json), requires `aspell`), `composer run rector` (dry-run refactoring for `src/` and `tests/`, configured in [rector.php](rector.php)), `composer run phpstan` (level 7, `src/` only, configured in [phpstan.neon](phpstan.neon)) and `composer run test`. Tests fail below 80% line coverage. All five gates must be clean. Run `composer run rector:fix` to apply Rector's proposed changes.

   `composer install` configures the repository's version-controlled pre-commit hook, which runs the same complete check before every commit. You can reinstall it manually with `composer run hooks:install`. Pull requests and pushes to `master` also run the check in GitHub Actions.

   `composer run test` and its alias `composer run coverage` write the browsable HTML report and machine-readable Clover report under `build/`. GitHub Actions publishes these reports as the `coverage-report` artifact and shows the text summary on the workflow run.

   A few tests exercise live resources and are skipped unless you opt in:

   Variable            | Enables
   ------------------- | -------
   `PHPLIB_MYSQL=1`    | live MySQL tests for `PDO_Connection` and `Dump`
   `PHPLIB_NETWORK=1`  | live HTTP tests for `Web_Service`

   The MySQL tests read `PHPLIB_MYSQL_HOST`, `PHPLIB_MYSQL_USER`, `PHPLIB_MYSQL_PASS` and `PHPLIB_MYSQL_NAME`, defaulting to `localhost`/`root` with an empty password. Run them if you touched either class.
8. **Open a pull request.** Push your `issues/` branch and open a PR against `master` with a clear description of what changed, why it changed, and how it was verified. Keep each PR scoped to a single purpose and reference the issue the branch is named for.

## Behaviour that is preserved deliberately

Some of what looks like a bug is a documented quirk that callers depend on. Each is commented at its site. **Do not "fix" these** in an unrelated PR — if one genuinely needs to change, that is its own issue and a breaking change:

* `User_Agent::detect_browser()` / `detect_device()` use `strpos()` as a truthy test, so a signature at offset 0 reads as no match.
* `Random::generate(0, 'INT')` returns `null` while `generate(0, 'STRING')` returns `''`.
* `Validation::extension()` also requires the `$type` to be in `$allowed_types`, so the two-argument form always returns `false`.
* `Directory_Lister::listing()` with `method => 'files'` returns `false` when no top-level file matches.
* `Sorter` records a configuration error and still attempts the copies.

`Date_Time_Format::$types`, `File::$image` and `File::$errors` are public API and stay public properties. Private configuration arrays are `private const`; do not promote the public ones.

If you are making a sweeping change across `src/` rather than a scoped one, build a parity harness first: dump the public surface to JSON before and after and diff it, comparing random and time-dependent values by shape or digit mask rather than by value. The PHP 8.5 modernisation was done under a 303-behaviour harness of exactly this kind.

## Known gaps

Contributions here are especially welcome, but each has a reason it is still open:

* `File::force_download()` ends in `readfile()` + `exit` behind a `headers_sent()` guard that is always false in CLI, so the emit path cannot be exercised in-process. Its input handling is tested; the emit path is not.
* `Website::image_size()` calls `getimagesize()` on whatever it is given, including remote URLs, so it can reach the network and warn on failure.
* PHPStan runs at level 7. Level 8 reports 14 null-safety findings whose fixes would change failure-path behaviour (e.g. `curl_setopt()` when `curl_init()` failed).

## Releasing (maintainers)

The package is published on [Packagist](https://packagist.org/packages/zlatanstajic/php-library) and served straight from the repository's git tags — [composer.json](composer.json) carries no `version` field, so **tagging is the release**.

1. Confirm `composer run check` is clean on `master`.
2. Tag the commit `vX.Y.Z` and push the tag, then create the matching GitHub release.
3. Confirm the new version appears on Packagist and that `composer require zlatanstajic/php-library` resolves to it from a clean directory.

By contributing you agree that your contributions are licensed under the repository's [MIT License](LICENSE.md). To reach the maintainer directly, use <contact@zlatanstajic.com>.
