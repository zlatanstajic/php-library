<?php

/**
 * Temperature
 *
 * Working with temperature conversions
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\Numericals;

/**
 * Working with temperature conversions
 */
class Temperature
{
    /**
     * Absolute zero value
     */
    private const float ABSOLUTE_ZERO = 273.15;

    /**
     * Temperature signs
     *
     * @var array<string, string>
     */
    private const array SIGNS = [
        'celsius' => '&degC',
        'fahrenheit' => 'F',
        'kelvin' => 'K',
    ];

    /**
     * Create return values
     *
     * @return array{value: float, rounded: int, signed: string}
     */
    private static function create_return_values(float $value, string $type): array
    {
        $sign = $value;
        $sign .= ' ';
        $sign .= self::SIGNS[$type];

        return [
            'value' => $value,
            'rounded' => intval(round($value)),
            'signed' => $sign,
        ];
    }

    /**
     * Kelvin to Celsius conversion
     *
     * @return array{value: float, rounded: int, signed: string}
     */
    public static function k_to_c(int|float|string $temp): array
    {
        $value = (floatval($temp) - self::ABSOLUTE_ZERO);

        return self::create_return_values($value, 'celsius');
    }

    /**
     * Kelvin to Fahrenheit conversion
     *
     * @return array{value: float, rounded: int, signed: string}
     */
    public static function k_to_f(int|float|string $temp): array
    {
        $value = ((floatval($temp) - self::ABSOLUTE_ZERO) * (9 / 5)) + 32;

        return self::create_return_values($value, 'fahrenheit');
    }

    /**
     * Fahrenheit to Celsius conversion
     *
     * @return array{value: float, rounded: int, signed: string}
     */
    public static function f_to_c(int|float|string $temp): array
    {
        $value = (floatval($temp) - 32) * (5 / 9);

        return self::create_return_values($value, 'celsius');
    }

    /**
     * Fahrenheit to Kelvin conversion
     *
     * @return array{value: float, rounded: int, signed: string}
     */
    public static function f_to_k(int|float|string $temp): array
    {
        $value = (floatval($temp) + 459.67) * (5 / 9);

        return self::create_return_values($value, 'kelvin');
    }

    /**
     * Celsius to Fahrenheit conversion
     *
     * @return array{value: float, rounded: int, signed: string}
     */
    public static function c_to_f(int|float|string $temp): array
    {
        $value = (floatval($temp) * (9 / 5)) + 32;

        return self::create_return_values($value, 'fahrenheit');
    }

    /**
     * Celsius to Kelvin conversion
     *
     * @return array{value: float, rounded: int, signed: string}
     */
    public static function c_to_k(int|float|string $temp): array
    {
        $value = floatval($temp) + self::ABSOLUTE_ZERO;

        return self::create_return_values($value, 'kelvin');
    }
}
