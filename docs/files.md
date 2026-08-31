# Files and spreadsheets

## Read and write files

```php
use PHP_Library\Core\Files\File;

File::write_to_file('/tmp/events.log', 'Started'); // append
File::write_to_file('/tmp/events.log', 'Header', false); // prepend

$lastLine = File::read_from_file('/tmp/events.log');
$rows = File::read_csv('/tmp/data.csv');

if ($rows['status']) {
    foreach ($rows['items'] as $columns) {
        // Semicolon-separated columns
    }
}
```

Configure image fallback behavior through the public property:

```php
File::$image = [
    'location' => '/var/www/images/',
    'default' => 'placeholder.png',
];

$path = File::image('profile.png');
```

`read_csv()` uses PHP's CSV parser, including quoted separators and multiline
fields. `read_file_contents()` is retained as a compatibility alias.

To serve a download without coupling the library to request termination, call
`File::prepare_download()`. It returns the path, filename and response headers
for the application or framework to emit. `force_download()` is deprecated.

## List directories

```php
use PHP_Library\Core\Files\Directory_Lister;

$result = Directory_Lister::listing([
    'directory' => __DIR__.'/src/',
    'method' => 'crawl',
    'types' => ['php'],
]);

foreach ($result['listing'] as $file) {
    echo $file['title'].PHP_EOL;
}
```

Methods are `crawl` for recursive traversal, `files` for top-level files and
`folders` for directories. The result contains `listing`, `count` and `max`;
`files` returns `false` when nothing matches. Results contain filesystem data
only; the former `print` behavior and embedded `open` HTML field were removed.

## Import spreadsheets

```php
use PHP_Library\Core\Files\Import;

$rows = Import::import_data('/tmp/orders.xlsx');

if ($rows !== false) {
    echo $rows[1]['A']; // first row, column A
}
```

Supported import types are returned by `Import::allowed_types()` and currently
include XLSX, XLS and CSV.

## Build or export spreadsheets

```php
use PHP_Library\Core\Files\Export;

$params = [
    'type' => 'xlsx',
    'head' => ['Code', 'Quantity'],
    'data' => [
        ['0042', 10],
        ['0100', 4],
    ],
    'data_types' => [
        ['index' => 0, 'type' => 'TEXT'],
    ],
    'document_properties' => [
        'creator' => 'Example application',
        'title' => 'Inventory',
    ],
];

$spreadsheet = Export::build($params);
// Inspect or save the PhpSpreadsheet object yourself.

Export::save($params, '/tmp/inventory.xlsx');
```

`Export::build()` returns a `PhpOffice\PhpSpreadsheet\Spreadsheet` without
emitting output, while `save()` writes to an explicit path. The process-ending
`export_file()` method is deprecated. Supported types are XLSX, XLS, CSV and
OSP.

## Sort files into folders

`Sorter` derives each destination folder from the first three characters of a
file name, then appends `folder_sufix`.

```php
use PHP_Library\Core\Files\Sorter;

$sorter = new Sorter([
    'where_to_read_files' => '/data/incoming/',
    'where_to_create_directories' => '/data/archive/',
    'folder_sufix' => '000',
    'number_of_directories' => 100,
    'types' => ['jpg', 'png'],
    'operation' => 'c', // c = copy, m = move
    'overwrite' => false,
]);

if (! $sorter->deploy()) {
    $errors = $sorter->get_error();
}

$report = $sorter->report();
```

Source and destination paths should include a trailing slash. The report
contains boolean state, a human-readable summary and detailed per-file results.
