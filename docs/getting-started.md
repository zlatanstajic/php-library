# Getting started

## Requirements

- PHP 8.5
- Composer 2
- The PHP extensions required by `phpoffice/phpspreadsheet` when using the
  spreadsheet helpers

## Installation

Install the current stable release:

```bash
composer require zlatanstajic/php-library
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

`composer check` runs formatting validation, PHPStan and Pest. Tests require
PCOV or Xdebug, generate reports under `build/`, and fail below 80% line
coverage. Composer also installs the repository's pre-commit hook so the same
gate runs before a commit.
