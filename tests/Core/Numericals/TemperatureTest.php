<?php

use PHP_Library\Core\Numericals\Temperature;

it('converts kelvin to celsius', function () {
    expect(Temperature::k_to_c(273.15))->toBe(['value' => 0.0, 'rounded' => 0, 'signed' => '0 &degC']);
    expect(Temperature::k_to_c(0)['value'])->toBe(-273.15);
});

it('converts celsius to kelvin', function () {
    expect(Temperature::c_to_k(0)['value'])->toBe(273.15);
    expect(Temperature::c_to_k(-273.15)['rounded'])->toBe(0);
});

it('converts celsius to fahrenheit at the known anchors', function () {
    expect(Temperature::c_to_f(0)['value'])->toBe(32.0);
    expect(Temperature::c_to_f(100)['value'])->toBe(212.0);
    expect(Temperature::c_to_f(-40)['value'])->toBe(-40.0);
});

it('converts fahrenheit to celsius at the known anchors', function () {
    expect(Temperature::f_to_c(32)['value'])->toBe(0.0);
    expect(Temperature::f_to_c(212)['value'])->toBe(100.0);
    expect(Temperature::f_to_c(-40)['value'])->toBe(-40.0);
});

it('converts fahrenheit to kelvin', function () {
    expect(round(Temperature::f_to_k(32)['value'], 2))->toBe(273.15);
});

it('converts kelvin to fahrenheit', function () {
    expect(round(Temperature::k_to_f(273.15)['value'], 2))->toBe(32.0);
});

it('always returns value, rounded and signed', function (string $method) {
    $result = Temperature::$method(20);

    expect($result)->toHaveKeys(['value', 'rounded', 'signed'])
        ->and($result['value'])->toBeFloat()
        ->and($result['rounded'])->toBeInt()
        ->and($result['signed'])->toBeString();
})->with(['k_to_c', 'k_to_f', 'f_to_c', 'f_to_k', 'c_to_f', 'c_to_k']);

it('labels each scale with its own sign', function () {
    expect(Temperature::c_to_f(0)['signed'])->toEndWith(' F')
        ->and(Temperature::c_to_k(0)['signed'])->toEndWith(' K')
        ->and(Temperature::k_to_c(0)['signed'])->toEndWith(' &degC');
});

it('accepts numeric strings, coercing like the untyped version did', function () {
    expect(Temperature::c_to_f('20'))->toBe(Temperature::c_to_f(20))
        ->and(Temperature::c_to_f('-17.8')['rounded'])->toBe(Temperature::c_to_f(-17.8)['rounded']);
});

it('rounds to the nearest integer', function () {
    expect(Temperature::c_to_f(36.6)['rounded'])->toBe(98);
});
