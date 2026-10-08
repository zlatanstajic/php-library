# Getting started

## Requirements

- PHP 8.5
- Composer 2
- The PHP extensions required by `phpoffice/phpspreadsheet`. Composer installs
  this runtime dependency even if you do not use the spreadsheet helpers and
  checks its extension requirements during installation.

HTTP services additionally require `ext-curl`, and PDO connections require
`ext-pdo_mysql`. MySQL dumps require the `mysqldump` executable and PHP's
`exec()` function.

## Installation

No stable version has been tagged yet. Install the development version from
`master`:

```bash
composer require zlatanstajic/php-library:dev-master
```

In a standalone script, load Composer before importing library classes:

```php
<?php

require __DIR__.'/vendor/autoload.php';

use PHP_Library\Core\Numericals\Math;

$percentage = Math::percentage(45, 60);

echo $percentage['sign']; // 75%
```

Frameworks that already load `vendor/autoload.php` need only the `use`
statement.

## Namespace map

| Concern | Namespace |
| --- | --- |
| Dates, email and formatting | `PHP_Library\Core\Arrangements` |
| Passwords, random values, validation, user agents | `PHP_Library\Core\Data` |
| Files, folders and spreadsheets | `PHP_Library\Core\Files` |
| Math and temperatures | `PHP_Library\Core\Numericals` |
| HTTP services | `PHP_Library\Core\Services` |
| Site metadata and assets | `PHP_Library\Core\Sites` |
| PDO connections and database dumps | `PHP_Library\Core\SQL` |

Class and method names intentionally retain the library's historical
`Pascal_Snake_Case` and `snake_case` API.

## Static and stateful classes

Most helpers are static:

```php
use PHP_Library\Core\Data\Random;
use PHP_Library\Core\Numericals\Temperature;

$token = Random::generate(32, 'STRING');
$fahrenheit = Temperature::c_to_f('20');

echo $fahrenheit['signed']; // 68 F
```

Integrations that need configuration are instantiated:

```php
use PHP_Library\Core\Services\Web_Service;

$service = new Web_Service('https://example.com/api/status');
$result = $service->response();
```

## Error handling

Stateful classes do not throw application errors. `Web_Service`, `Website`,
`Sorter`, `Dump` and `PDO_Connection` expose message getters inherited from the
system layer:

```php
if ($service->has_errors()) {
    foreach ($service->get_error() as $message) {
        error_log($message);
    }
}
```

Available getters are `get_message()`, `get_success()`, `get_error()` and
`get_file()`. Check the documented return value as well: many operations use
`false` for an invalid or unavailable result.

```{note}
The library deliberately uses PHP's coercive mode. Do not add
`declare(strict_types=1)` to library files when contributing; numeric-string
inputs are part of the public contract.
```

## Development checks

```bash
composer install
composer check
```

`composer check` runs Pint formatting validation, Peck spelling checks, Rector
in dry-run mode, PHPStan and Pest, in that order, stopping at the first failure.
Peck requires `aspell` and an English dictionary. Tests require PCOV or Xdebug,
generate reports under `build/`, and fail below 80% line coverage. Composer also
installs the repository's pre-commit hook so the same gate runs before a commit.

To build the documentation, install its pinned dependencies in a Python virtual
environment and run the same command as CI:

```bash
python -m pip install --requirement docs/requirements.txt
sphinx-build --fail-on-warning --builder html docs docs/_build/html
```

Pull requests build the documentation without deploying it. Pushes to `master`
and manual dispatches on `master` publish the successful build to GitHub Pages.
See [Security and trusted inputs](security.md) for integration boundaries and
private vulnerability reporting.
