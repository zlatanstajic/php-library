<?php

use PHP_Library\Core\Data\Random;

it('generates a numeric sequence of the requested length', function () {
    $value = Random::generate(8, 'INT');

    expect($value)->toBeString()
        ->and(strlen($value))->toBe(8)
        ->and($value)->toMatch('/^[0-9]{8}$/');
});

it('generates an alphanumeric sequence of the requested length', function () {
    $value = Random::generate(12, 'STRING');

    expect(strlen($value))->toBe(12)
        ->and($value)->toMatch('/^[A-Za-z0-9]{12}$/');
});

it('generates a pronounceable sequence alternating consonant and vocal', function () {
    $value = Random::generate(8, 'STRING_ADVANCED');

    expect(strlen($value))->toBe(8)
        ->and($value)->toMatch('/^[A-Z][aeiou]([b-z][aeiou]){3}$/');
});

it('returns false for an unknown type', function () {
    expect(Random::generate(4, 'NOPE'))->toBeFalse();
});

it('preserves the legacy empty-length quirk', function () {
    // INT seeds with NULL, STRING seeds with '' - deliberately unchanged.
    expect(Random::generate(0, 'INT'))->toBeNull()
        ->and(Random::generate(0, 'STRING'))->toBe('');
});

it('draws from the whole numeric alphabet, not a biased subset', function () {
    $seen = [];

    for ($i = 0; $i < 400; $i++) {
        $seen[Random::generate(1, 'INT')] = true;
    }

    expect(count($seen))->toBe(10);
});

it('does not repeat itself across calls', function () {
    $values = [];

    for ($i = 0; $i < 50; $i++) {
        $values[] = Random::generate(16, 'STRING');
    }

    expect(count(array_unique($values)))->toBe(50);
});

it('returns the only element of a single-item list', function () {
    expect(Random::element(['only']))->toBe('only');
});

it('returns null for an empty list', function () {
    expect(Random::element([]))->toBeNull();
});

it('returns an element that is actually in the list', function () {
    $list = [
        'a',
        'b',
        'c',
        'd',
    ];

    for ($i = 0; $i < 30; $i++) {
        expect($list)->toContain(Random::element($list));
    }
});

it('indexes by weekday for the DAY dose', function () {
    $list = range(1, 7);

    expect(Random::element($list, 'DAY'))->toBe((int) date('N'));
});

it('indexes by day of month for the MONTH dose', function () {
    $list = range(1, 31);

    expect(Random::element($list, 'MONTH'))->toBe((int) date('j'));
});

it('falls back to random when the list is too short for the dose', function () {
    $list = [
        'a',
        'b',
    ];

    expect($list)->toContain(Random::element($list, 'DAY'));
});

it('builds a cache-busting query fragment', function () {
    expect(Random::break_caching())->toMatch('/^\?break_caching=\d+$/');
});
