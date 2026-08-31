# API reference

This is a compact signature index. See the task-focused guides for examples and
return shapes.

## Arrangements

### `Date_Time_Format`

`current`, `compare`, `format`, `format_to_database`, `format_to_user`,
`minutes_to_hours`, `hours_to_minutes`, `number_to_day`, `number_to_month`,
`prefix`, `date_from_jmbg`, `first_day_of_year`, `days_before`, `days_after`,
`get_days`, `get_months` and public format catalogue `$types`.

### `Email`

```php
Email::show(?string $email): string|false
Email::mailto(?string $email, string $text = '', string $subject = '', array|string $attributes = ''): string|false
Email::validate(?string $email, array $invalid_email_clients = []): string|false
```

### `Format`

`bytes`, `query`, `telephone`, `website`, `ip`, `title_case`, `number`, `debug`, `pre`,
`windows1250_to_utf8`, `utf8_to_windows1250`, `string`, `price_format`,
`array_to_string`, `fullname`, `search_clause`, `in_clause`, `search_wizard`,
`language_value` and `in_wizard`.

## Data

### `Password`

`new_unreadable`, `new_readable`, `meets_policy`, `hash`, `verify`, `strength`,
`encode`, `decode`, `digest` and `set_method`.

### `Random`

```php
Random::generate(int $length = 4, string $type = 'INT'): string|false|null
Random::element(array $list, string $dose = ''): mixed
Random::break_caching(): string
```

### `User_Agent`

`list_browsers`, `list_operating_systems`, `list_devices`, `list_crawlers`,
`detect_browser`, `detect_operating_system`, `detect_device`, `is_mobile` and
`is_crawler`.

### `Validation`

`year`, `comma`, `clear_string`, `clear_number`, `rewrite`, `rewrite_special`
and `extension`.

## Files

### `File`

`image`, `write_to_file`, `read_from_file`, `read_csv`, `prepare_download`,
`read_file_contents` and `force_download`. Public configuration/status
properties: `$image`, `$errors`.

### `Directory_Lister`

```php
Directory_Lister::listing(array $params): mixed
```

### `Import` and `Export`

```php
Import::allowed_types(): array
Import::import_data(string $file_path): array|false

Export::allowed_types(): array
Export::build(array $params): ?PhpOffice\PhpSpreadsheet\Spreadsheet
Export::save(array $params, string $path): bool
Export::export_file(array $params): void
```

### `Sorter`

```php
new Sorter(array $params)
$sorter->deploy(): bool
$sorter->report(): array
```

## Numericals

### `Math`

`percentage`, `by_parity`, `even_or_odd`, `iterate`, `set_iterator` and
`get_iterator`.

### `Temperature`

`k_to_c`, `k_to_f`, `f_to_c`, `f_to_k`, `c_to_f` and `c_to_k`.

## Web and sites

### `Web_Service`

```php
new Web_Service(string $url = '')
$service->set_url(string $url): void
$service->response(array $params = []): array|false
```

### `Website`

`add_to_head`, `add_to_bottom`, `add_to_images`, `add_to_creator`, `meta`,
`head`, `bottom`, `creator`, `images`, `image_size`, `signature`,
`signature_hidden`, `get_server`, `get_name`, `get_host`, `get_made`,
`get_language`, `get_charset`, `get_description` and `get_keywords`.

## SQL

```php
new PDO_Connection(string $host = '', string $user = '', string $pass = '', string $name = '')
$connection->get_connection(): ?object

new Dump(array $params)
$dump->mysql(bool $override = false): bool
```

## Messages

Stateful classes expose:

```php
$object->get_message(): array
$object->get_success(): array
$object->get_error(): array
$object->get_file(): array
$object->has_errors(): bool
```

Classes that inherit the internal `Testing` layer also expose `turn_on()`. It is
a test seam for forcing unavailable-function branches and is not intended for
application code.
