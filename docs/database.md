# Database

## PDO connections

```php
use PHP_Library\Core\SQL\PDO_Connection;

$database = new PDO_Connection(
    getenv('DB_HOST') ?: 'localhost',
    getenv('DB_USER') ?: 'root',
    getenv('DB_PASS') ?: '',
    getenv('DB_NAME') ?: ''
);

$pdo = $database->get_connection();

if ($pdo === null) {
    foreach ($database->get_error() as $error) {
        error_log($error);
    }
}
```

Connection failures are caught and recorded. `get_connection()` returns a PDO
object on success and `null` on failure.

## MySQL dumps

`Dump` invokes `mysqldump` and writes one timestamped SQL file per database.
Pass secrets through environment variables rather than source code.

```php
use PHP_Library\Core\SQL\Dump;

$dump = new Dump([
    'destination' => '/var/backups/mysql/',
    'connection' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
    ],
    'databases' => ['app', 'analytics'],
]);

if (! $dump->mysql()) {
    foreach ($dump->get_error() as $error) {
        error_log($error);
    }
}

$createdFiles = $dump->get_file();
```

The password is passed through `MYSQL_PWD`, not exposed in the process command
line. Database names and connection arguments are escaped. The destination
directory must already exist and be writable.
