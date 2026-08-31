<?php

/**
 * Validation
 *
 * Validation methods
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\Data;

/**
 * Validation methods
 */
class Validation
{
    /**
     * Characters stripped out by clear_string
     *
     * @var list<string>
     */
    private const array STRIPPED_CHARACTERS = [
        '"',
        "'",
        '(',
        ')',
        '/',
        ';',
        '*',
        '>',
        '<',
    ];

    /**
     * Special characters folded down to ASCII by rewrite_special
     *
     * @var array<string, string>
     */
    private const array SPECIAL_CHARACTERS = [
        'Ć' => 'ć',
        'Č' => 'č',
        'Ž' => 'ž',
        'Š' => 'š',
    ];

    /**
     * Validates year format
     */
    public static function year(mixed $year): bool
    {
        return is_numeric($year) && strlen(strval($year)) === strlen(date('Y'));
    }

    /**
     * Replaces comma with dot
     *
     * Values without a comma are handed back untouched, type included.
     *
     * @deprecated Use str_replace() at the input boundary.
     */
    public static function comma(int|float|string $param): int|float|string
    {
        if (str_contains(strval($param), ',')) {
            return str_replace(',', '.', strval($param));
        }

        return $param;
    }

    /**
     * Clears string of special characters
     */
    public static function clear_string(?string $variable, bool $to_trim = true): string|false
    {
        if (! empty($variable)) {
            if ($to_trim) {
                $variable = trim($variable);
            }

            return str_ireplace(self::STRIPPED_CHARACTERS, '', $variable);
        }

        return false;
    }

    /**
     * Clears integer - returns zero if not proper
     *
     * @deprecated Use an explicit numeric validation and integer cast.
     */
    public static function clear_number(mixed $variable): int
    {
        return is_numeric($variable) ? intval($variable) : 0;
    }

    /**
     * Rewriting string parameters
     */
    public static function rewrite(?string $string): string|false|null
    {
        if (! empty($string)) {
            $string = strtolower($string);
            $string = str_replace(' ', '_', $string);
            $string = preg_replace('/[^a-z-0-9-.]+/', '_', $string);

            return $string;
        }

        return false;
    }

    /**
     * Rewriting string parameters with special characters
     */
    public static function rewrite_special(?string $string): string|false
    {
        if (! empty($string)) {
            $string_replaced = strtr($string, self::SPECIAL_CHARACTERS);
            $string_lowered = strtolower($string_replaced);

            $string_lowered = str_ireplace(
                [
                    'ć',
                    'č',
                    'ž',
                    'š',
                    'đ',
                    'Đ',
                ],
                [
                    'c',
                    'c',
                    'z',
                    's',
                    'dj',
                    'dj',
                ],
                $string_lowered
            );

            $string_replaced = preg_replace('/_[a-zA-Z0-9]+(\.)/', '.', $string_lowered, 1);
            $string_trimmed = trim(strval($string_replaced));

            $string_trimmed = str_ireplace(' ', '_', $string_trimmed);
            $string_trimmed = str_ireplace('__', '_', $string_trimmed);
            $string_trimmed = str_ireplace('___', '_', $string_trimmed);
            $string_trimmed = str_ireplace(['(', ')', '"', "'"], '', $string_trimmed);
            $string_trimmed = str_ireplace(' ', '_', $string_trimmed);
            $string_trimmed = str_ireplace(['(', ')', '%'], '', $string_trimmed);

            return $string_trimmed;
        }

        return false;
    }

    /**
     * Checks if file extension is valid or not
     *
     * @param  list<string>  $allowed_extensions
     * @param  list<string>  $allowed_types
     */
    public static function extension(
        string $file,
        array $allowed_extensions,
        string $type = '',
        array $allowed_types = []
    ): bool {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $extension = strtolower($extension);

        return in_array($extension, $allowed_extensions) &&
            in_array($type, $allowed_types);
    }
}
