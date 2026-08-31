<?php

use PHP_Library\Core\Data\Password;

afterEach(fn () => Password::set_method('sha512'));

it('generates an unreadable password of the default length', function () {
    expect(strlen(Password::new_unreadable()))->toBe(9);
});

it('honours a requested length', function () {
    expect(strlen(Password::new_unreadable(20)))->toBe(20);
});

it('draws unreadable passwords without repeating a character', function () {
    // str_shuffle() was a permutation; the CSPRNG replacement must stay one.
    $value = Password::new_unreadable(20);

    expect(strlen(count_chars($value, 3)))->toBe(strlen($value));
});

it('uses only the supplied alphabet', function () {
    expect(Password::new_unreadable(5, 'abc'))->toMatch('/^[abc]{3}$/');
});

it('does not repeat unreadable passwords', function () {
    $values = [];

    for ($i = 0; $i < 50; $i++) {
        $values[] = Password::new_unreadable();
    }

    expect(count(array_unique($values)))->toBe(50);
});

it('generates a readable password ending in four digits', function () {
    expect(Password::new_readable())->toMatch('/^[a-z]+\d{4}$/')
        ->and(strlen(Password::new_readable()))->toBe(9);
});

it('builds readable passwords from the supplied words', function () {
    // Words are concatenated then truncated to make room for the 4-digit
    // suffix, so the tail word can be cut mid-way ("alphaalp" + "7824").
    $value = Password::new_readable(12, 'alpha,beta');

    expect($value)->toMatch('/^(alpha|beta)[a-z]*\d{4}$/')
        ->and(strlen($value))->toBe(12);
});

it('round-trips encode and decode', function (string $plain) {
    expect(Password::decode(Password::encode($plain)))->toBe($plain);
})->with(['hello', '', 'a b c', 'Ćao šta', "line\nbreak", 'symbols +/=']);

it('produces url-safe encoded output', function () {
    expect(Password::encode('any input at all ????'))->not->toContain('+')
        ->and(Password::encode('any input at all ????'))->not->toContain('/')
        ->and(Password::encode('any input at all ????'))->not->toContain('=');
});

it('digests with the configured method', function () {
    expect(Password::digest('x'))->toBe(hash('sha512', 'x'));

    Password::set_method('sha256');

    expect(Password::digest('x'))->toBe(hash('sha256', 'x'));
});

it('refuses to digest empty input', function () {
    expect(Password::digest(''))->toBeFalse();
});

it('refuses an unknown digest method', function () {
    Password::set_method('not-a-real-method');

    expect(Password::digest('x'))->toBeFalse();
});

it('scores a short password as zero strength', function () {
    expect(Password::strength('abc'))->toBe(['status' => false, 'strength' => 0]);
});

it('scores a varied password above a repetitive one', function () {
    $varied = Password::strength('Abc123!@#XYZ')['strength'];
    $repetitive = Password::strength('aaaaaaaaaa')['strength'];

    expect($varied)->toBeGreaterThan($repetitive);
});

it('reports status against the given threshold', function () {
    expect(Password::strength('Abc123!@#XYZ', 0)['status'])->toBeTrue()
        ->and(Password::strength('Abc123!@#XYZ', 100)['status'])->toBeFalse();
});

it('never reports strength above 100', function () {
    expect(Password::strength(str_repeat('aA1!bB2@cC3#', 30))['strength'])->toBeLessThanOrEqual(100);
});

it('hashes and verifies passwords with the native password API', function () {
    $hash = Password::hash('correct horse battery staple');

    expect($hash)->not->toBe('correct horse battery staple')
        ->and(Password::verify('correct horse battery staple', $hash))->toBeTrue()
        ->and(Password::verify('wrong', $hash))->toBeFalse();
});

it('checks the documented baseline password policy', function () {
    expect(Password::meets_policy('LongEnough1!'))->toBeTrue()
        ->and(Password::meets_policy('alllowercase1!'))->toBeFalse()
        ->and(Password::meets_policy('SHORT1!', 12))->toBeFalse();
});
