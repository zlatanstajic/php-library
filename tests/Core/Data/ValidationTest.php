<?php

use PHP_Library\Core\Data\Validation;

it('accepts a four-digit year', function () {
    expect(Validation::year(2020))->toBeTrue()
        ->and(Validation::year('1999'))->toBeTrue();
});

it('rejects anything that is not a four-digit number', function (mixed $input) {
    expect(Validation::year($input))->toBeFalse();
})->with(['abc', 0, 999, 12345, '', null]);

it('swaps a decimal comma for a dot', function () {
    expect(Validation::comma('1,5'))->toBe('1.5');
});

it('leaves comma-free values untouched, type included', function () {
    expect(Validation::comma('1.5'))->toBe('1.5')
        ->and(Validation::comma(42))->toBe(42);
});

it('strips the characters it considers unsafe', function () {
    expect(Validation::clear_string('a"b\'c(d)e/f;g*h>i<j'))->toBe('abcdefghij');
});

it('trims by default and can be told not to', function () {
    expect(Validation::clear_string('  hi  '))->toBe('hi')
        ->and(Validation::clear_string('  hi  ', false))->toBe('  hi  ');
});

it('returns false for empty input', function () {
    expect(Validation::clear_string(''))->toBeFalse()
        ->and(Validation::clear_string(null))->toBeFalse();
});

it('reduces a value to an integer or zero', function () {
    expect(Validation::clear_number('42'))->toBe(42)
        ->and(Validation::clear_number('3.9'))->toBe(3)
        ->and(Validation::clear_number('a1b2'))->toBe(0)
        ->and(Validation::clear_number(''))->toBe(0);
});

it('rewrites a string to a lowercase slug', function () {
    expect(Validation::rewrite('Hello World'))->toBe('hello_world');
});

it('replaces runs of unsupported characters when rewriting', function () {
    expect(Validation::rewrite('a@@@b'))->toBe('a_b');
});

it('returns false when rewriting empty input', function () {
    expect(Validation::rewrite(''))->toBeFalse()
        ->and(Validation::rewrite_special(''))->toBeFalse();
});

it('folds serbian diacritics down to ascii', function () {
    expect(Validation::rewrite_special('ĆČŽŠ'))->toBe('cczs')
        ->and(Validation::rewrite_special('đak'))->toBe('djak');
});

it('collapses spaces and drops brackets when rewriting specially', function () {
    expect(Validation::rewrite_special('a (b) c'))->toBe('a_b_c');
});

it('validates an extension only when the type is also allowed', function () {
    expect(Validation::extension('a.jpg', ['jpg', 'png'], 'image', ['image']))->toBeTrue()
        ->and(Validation::extension('a.exe', ['jpg', 'png'], 'image', ['image']))->toBeFalse();
});

it('is case-insensitive about the extension', function () {
    expect(Validation::extension('A.JPG', ['jpg'], 'image', ['image']))->toBeTrue();
});

it('fails when no allowed types are supplied', function () {
    // Documented legacy behaviour: the type check runs even with defaults.
    expect(Validation::extension('a.jpg', ['jpg']))->toBeFalse();
});
