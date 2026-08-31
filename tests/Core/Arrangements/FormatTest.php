<?php

use PHP_Library\Core\Arrangements\Format;

it('formats bytes with the right unit', function (int $bytes, string $unit) {
    expect(Format::bytes($bytes)['sign'])->toEndWith(' '.$unit);
})->with([
    [
        0,
        'B',
    ],
    [
        512,
        'B',
    ],
    [
        2048,
        'kB',
    ],
    [
        5 * 1024 * 1024,
        'MB',
    ],
    [
        3 * 1024 ** 3,
        'GB',
    ],
]);

it('rounds bytes by default and can be told not to', function () {
    expect(Format::bytes(1536)['value'])->toBe(1.5)
        ->and(Format::bytes(1500, false)['value'])->toBeGreaterThan(1.46);
});

it('clamps negative byte counts to zero', function () {
    expect(Format::bytes(-100)['value'])->toBe(0.0);
});

it('caps the unit at the largest known one', function () {
    expect(Format::bytes(1024 ** 6)['sign'])->toEndWith(' TB');
});

it('escapes angle brackets when formatting a query', function () {
    expect(Format::query('a < b > c "quoted"'))
        ->toBe('<pre><code>a &lt; b &gt; c &quot;quoted&quot;</code></pre>');
});

it('formats a telephone number into blocks', function () {
    expect(Format::telephone('060 123 45 67'))->toBe('060/12-34-567');
});

it('falls back to the backup number', function () {
    expect(Format::telephone('', '060 123 4567'))->toBe('060/12-34-567');
});

it('returns false when there is no telephone at all', function () {
    expect(Format::telephone(''))->toBeFalse();
});

it('builds an anchor for a bare domain', function () {
    $site = Format::website('example.com');

    expect($site['name'])->toBe('http://www.example.com')
        ->and($site['anchor'])->toContain('rel="noopener"')
        ->and($site['anchor'])->toContain('>example.com</a>');
});

it('uses https when asked', function () {
    expect(Format::website('example.com', true)['name'])->toBe('https://www.example.com');
});

it('leaves an already-absolute url alone', function () {
    expect(Format::website('https://example.com')['name'])->toBe('https://example.com');
});

it('supports modern top-level domains and escapes generated markup', function () {
    $site = Format::website('example.technology/path?one=1&two=2', true);

    expect($site['name'])->toBe('https://www.example.technology/path?one=1&two=2')
        ->and($site['anchor'])->toContain('one=1&amp;two=2');
});

it('rejects a value that is not a website', function () {
    expect(Format::website('not a website at all !!'))->toBeFalse();
});

it('names loopback addresses instead of linking them', function () {
    expect(Format::ip('127.0.0.1'))->toBe('Localhost')
        ->and(Format::ip('::1'))->toBe('Localhost');
});

it('links a routable ip to the locator', function () {
    expect(Format::ip('8.8.8.8'))->toContain('geoplugin.net')
        ->and(Format::ip('8.8.8.8'))->toContain('>8.8.8.8</a>');
});

it('returns false for an empty ip', function () {
    expect(Format::ip(''))->toBeFalse();
});

it('title-cases a sentence', function () {
    expect(Format::title_case('hELLO wORLD'))->toBe('Hello World')
        ->and(Format::title_case('ĆAO SVIJETE'))->toBe('Ćao Svijete');
});

it('abbreviates large numbers', function () {
    expect(Format::number(1500000))->toBe('1.5')
        ->and(Format::number(2000000, false))->toBe('2');
});

it('returns an empty string for a zero number', function () {
    expect(Format::number(0))->toBe('');
});

it('shortens a long string with an ellipsis', function () {
    expect(Format::string('abcdefghijklmnopqrstuvwxyz'))->toBe('abcdefghijklmno...');
});

it('leaves a short string intact', function () {
    expect(Format::string('short'))->toBe('short');
});

it('strips tags before shortening', function () {
    expect(Format::string('<b>bold</b>'))->toBe('bold');
});

it('formats a price with comma decimals', function () {
    expect(Format::price_format(1234.5))->toBe('1.234,50')
        ->and(Format::price_format(1234.5, 0))->toBe('1.235');
});

it('refuses a price that already contains a comma', function () {
    expect(Format::price_format('1,5'))->toBeFalse();
});

it('joins an array with a separator', function () {
    expect(Format::array_to_string(['a', 'b', 'c']))->toBe('a|b|c')
        ->and(Format::array_to_string(['a', 'b'], ', '))->toBe('a, b')
        ->and(Format::array_to_string([]))->toBe('');
});

it('joins a name and surname', function () {
    expect(Format::fullname('Ada', 'Lovelace'))->toBe('Ada Lovelace')
        ->and(Format::fullname('Ada', 'Lovelace', ', '))->toBe('Ada, Lovelace');
});

it('converts between windows-1250 and utf-8', function () {
    $utf = 'Cao';

    expect(Format::windows1250_to_utf8(Format::utf8_to_windows1250($utf)))->toBe($utf);
});

it('builds a LIKE clause per term and field', function () {
    $sql = Format::search_wizard('foo bar', ['name', 'title']);

    expect($sql)->toContain("name LIKE ('%foo%')")
        ->and($sql)->toContain("OR title LIKE ('%bar%')")
        ->and(substr_count($sql, 'AND ('))->toBe(2);
});

it('returns false from the wizards when input is missing', function () {
    expect(Format::search_wizard('', ['a']))->toBeFalse()
        ->and(Format::search_wizard('a', []))->toBeFalse()
        ->and(Format::in_wizard('', ['a']))->toBeFalse()
        ->and(Format::in_wizard('a', []))->toBeFalse();
});

it('builds an IN clause', function () {
    expect(Format::in_wizard('status', ['new', 'open']))->toBe(" AND status IN ('new', 'open')");
});

it('builds a parameterized LIKE clause and bindings', function () {
    $clause = Format::search_clause('foo bar', ['users.name', 'title']);

    expect($clause['sql'])->toContain('users.name LIKE :search_0_0')
        ->and($clause['sql'])->toContain('title LIKE :search_1_1')
        ->and($clause['bindings'])->toBe([
            'search_0_0' => '%foo%',
            'search_0_1' => '%foo%',
            'search_1_0' => '%bar%',
            'search_1_1' => '%bar%',
        ]);
});

it('builds a parameterized IN clause and bindings', function () {
    expect(Format::in_clause('status', ['new', "open' OR 1=1 --"]))->toBe([
        'sql' => ' AND status IN (:in_0, :in_1)',
        'bindings' => [
            'in_0' => 'new',
            'in_1' => "open' OR 1=1 --",
        ],
    ]);
});

it('rejects unsafe SQL identifiers', function () {
    expect(Format::search_clause('foo', ['name; DROP TABLE users']))->toBeFalse()
        ->and(Format::in_clause('status OR 1=1', ['open']))->toBeFalse()
        ->and(Format::search_wizard('foo', ['name; DROP TABLE users']))->toBeFalse()
        ->and(Format::in_wizard('status OR 1=1', ['open']))->toBeFalse();
});

it('picks the secondary value for serbian and the primary otherwise', function () {
    expect(Format::language_value('serbian', 'yes', 'da'))->toBe('da')
        ->and(Format::language_value('english', 'yes', 'da'))->toBe('yes')
        ->and(Format::language_value('serbian', 'yes'))->toBe('yes');
});

it('prints nothing when told not to print', function () {
    ob_start();
    Format::pre(['a' => 1], false);

    expect(ob_get_clean())->toBe('');
});

it('wraps printed data in a pre block', function () {
    ob_start();
    Format::pre(['a' => 1]);
    $out = ob_get_clean();

    expect($out)->toStartWith('<pre>')->and($out)->toContain('[a] =&gt; 1');
});

it('returns escaped debug markup without printing it', function () {
    ob_start();
    $markup = Format::debug(['value' => '<script>']);

    expect(ob_get_clean())->toBe('')
        ->and($markup)->toContain('&lt;script&gt;');
});
