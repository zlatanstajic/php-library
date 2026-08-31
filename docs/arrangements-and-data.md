# Arrangements and data

## Dates and times

`Date_Time_Format` converts the library's user (`d.m.Y`) and database (`Y-m-d`)
formats and provides common calendar helpers.

```php
use PHP_Library\Core\Arrangements\Date_Time_Format as DateTime;

$stored = DateTime::format_to_database('05.03.2020'); // 2020-03-05
$shown = DateTime::format_to_user('2020-03-05');      // 05.03.2020
$friendly = DateTime::format('2020-03-05 14:30:00'); // 05-Mar-2020 14:30:00

$duration = DateTime::minutes_to_hours(90); // 01:30
$minutes = DateTime::hours_to_minutes('01:30'); // 90

$days = DateTime::get_days('english', 3)['php'];
$monthsJson = DateTime::get_months('serbian')['json'];
```

Sentinel and empty dates return `false` from the format conversion methods.
`get_days()` and `get_months()` return both a PHP array under `php` and its JSON
representation under `json`.

## Email

```php
use PHP_Library\Core\Arrangements\Email;

$address = Email::validate('person@example.com');
$safeText = Email::show('person@example.com');
$safeLink = Email::mailto(
    'person@example.com',
    'Contact us',
    'Question about PHP Library',
    ['class' => 'contact']
);
```

`validate()` delegates syntax checking to PHP's email filter and returns the
address or `false`. `show()` entity-encodes the address and `mailto()` returns a
normal escaped anchor without JavaScript. Pass an explicit invalid-provider
list when the application maintains one; the library does not ship a stale
provider list.

## General formatting

```php
use PHP_Library\Core\Arrangements\Format;

$size = Format::bytes(5 * 1024 * 1024);
// ['value' => 5.0, 'sign' => '5 MB']

$url = Format::website('example.com', true);
echo $url['name'];   // https://www.example.com
echo $url['anchor']; // ready-to-render anchor

echo Format::price_format(1234.5);          // 1.234,50
echo Format::string('A very long sentence', 0, 10); // A very lon...
```

Other helpers cover telephone numbers, IP links, title casing, character-set
conversion and language-dependent values. URL parsing uses PHP 8.5's URI
implementation and accepts modern top-level domains.

Use the parameterized SQL builders with PDO:

```php
$search = Format::search_clause('ada engineer', ['users.name', 'users.role']);
$statement = $pdo->prepare('SELECT * FROM users WHERE 1=1'.$search['sql']);
$statement->execute($search['bindings']);

$statuses = Format::in_clause('status', ['new', 'open']);
```

`search_wizard()` and `in_wizard()` remain as escaped compatibility helpers but
are deprecated because a string-only API cannot carry bindings.

## Validation and slugs

```php
use PHP_Library\Core\Data\Validation;

Validation::year('2026');                    // true
Validation::rewrite('Hello World');          // hello_world
Validation::rewrite_special('Ćao svete');    // cao_svete

$allowed = Validation::extension(
    'photo.JPG',
    ['jpg', 'png'],
    'image',
    ['image']
); // true
```

## Passwords and random values

```php
use PHP_Library\Core\Data\Password;
use PHP_Library\Core\Data\Random;

$password = Password::new_unreadable(20);
$readable = Password::new_readable(16, 'alpha,beta,gamma');
$meetsPolicy = Password::meets_policy('LongEnough1!');
$hash = Password::hash('LongEnough1!');
$valid = Password::verify('LongEnough1!', $hash);

$code = Random::generate(6, 'INT');
$token = Random::generate(32, 'STRING');
$wordLike = Random::generate(10, 'STRING_ADVANCED');
$item = Random::element(['red', 'green', 'blue']);
```

Password and random generation use PHP's secure `Random\Randomizer` API.
`strength()`, `encode()`, `decode()`, `digest()` and `set_method()` are retained
for compatibility but deprecated. `encode()` is reversible and `digest()` is a
generic checksum; neither is password storage.

## Legacy user-agent detection

```php
use PHP_Library\Core\Data\User_Agent;

$agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

$browser = User_Agent::detect_browser($agent, 'Unknown');
$os = User_Agent::detect_operating_system($agent, 'Unknown');
$device = User_Agent::detect_device($agent, 'Unknown');
$mobile = User_Agent::is_mobile($agent);
$crawler = User_Agent::is_crawler($agent);
```

The entire `User_Agent` class is deprecated. User-agent sniffing is unreliable;
prefer feature detection for behavior and a maintained parser or User-Agent
Client Hints when identification is unavoidable. The compatibility parser does
recognize common modern Edge, Opera, iOS, Android and crawler signatures, but it
must never be used as a security boundary.
