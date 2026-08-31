<?php

/**
 * Format
 *
 * Format related methods
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\Arrangements;

use Throwable;
use Uri\Rfc3986\Uri;

/**
 * Format related methods
 */
class Format
{
    /**
     * UTF-8 value
     */
    private const string UTF_8 = 'utf-8';

    /**
     * Windows-1250 value
     */
    private const string WINDOWS_1250 = 'windows-1250';

    /**
     * IP related values
     *
     * @var array{locator: string, localhost: array{name: string, addresses: list<string>}}
     */
    private const array IP = [
        'locator' => 'http://www.geoplugin.net/php.gp?ip=',
        'localhost' => [
            'name' => 'Localhost',
            'addresses' => [
                '::1',
                '127.0.0.1',
            ],
        ],
    ];

    /**
     * Website related values
     *
     * @var array{web: string, protocol: array{unsafe: string, safe: string}}
     */
    private const array WEBSITE = [
        'web' => 'www',
        'protocol' => [
            'unsafe' => 'http://',
            'safe' => 'https://',
        ],
    ];

    /**
     * Computer digital information units
     *
     * @var list<string>
     */
    private const array UNITS = [
        'B',
        'kB',
        'MB',
        'GB',
        'TB',
    ];

    /**
     * Converts bytes
     *
     * @return array{value: int|float, sign: string}
     */
    public static function bytes(int|float $bytes, bool $to_round = true, int $round_precision = 2): array
    {
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = (int) min($pow, count(self::UNITS) - 1);
        $bytes = $bytes / 1024 ** $pow;

        if ($to_round) {
            $bytes = round($bytes, $round_precision);
        }

        return [
            'value' => $bytes,
            'sign' => $bytes.' '.self::UNITS[$pow],
        ];
    }

    /**
     * Formatting query
     */
    public static function query(string $query): string
    {
        return '<pre><code>'.htmlspecialchars($query, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</code></pre>';
    }

    /**
     * Formats telephone number
     */
    public static function telephone(string $telephone = '', string $telephone_backup = ''): string|false
    {
        if (empty($telephone)) {
            $telephone = $telephone_backup;
        }

        if (! empty($telephone)) {
            $telephone_print = '';
            $exploded_telephone = explode(' ', $telephone);

            foreach ($exploded_telephone as $row) {
                $telephone = trim($row);
                $telephone = preg_replace('/[^0-9,.]/', '', $telephone);

                $telephone_print .= $telephone;
            }

            $first = substr($telephone_print, 0, 3);
            $second = substr($telephone_print, 3, 2);
            $third = substr($telephone_print, 5, 2);
            $fourth = substr($telephone_print, 7, 5);

            return $first.'/'.$second.'-'.$third.'-'.$fourth;
        }

        return false;
    }

    /**
     * Formats website URL
     *
     * @return array{name: string, anchor: string}|false
     */
    public static function website(string $location, bool $is_ssl = false): array|false
    {
        $location = trim($location);

        if ($location === '') {
            return false;
        }

        $has_scheme = preg_match('#^[a-z][a-z0-9+.-]*://#i', $location) === 1;
        $location_final = $location;

        if (! $has_scheme) {
            $prefix = $is_ssl
                ? self::WEBSITE['protocol']['safe']
                : self::WEBSITE['protocol']['unsafe'];

            if (! str_starts_with(strtolower($location), self::WEBSITE['web'].'.')) {
                $prefix .= self::WEBSITE['web'].'.';
            }

            $location_final = $prefix.$location;
        }

        try {
            $uri = new Uri($location_final);
        } catch (Throwable) {
            return false;
        }

        if (! in_array(strtolower($uri->getScheme()), ['http', 'https'], true) || $uri->getHost() === null) {
            return false;
        }

        $location_final = $uri->toString();
        $escaped_url = htmlspecialchars($location_final, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $escaped_label = htmlspecialchars($location, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return [
            'name' => $location_final,
            'anchor' => '<a href="'.$escaped_url.'" target="_blank" rel="noopener">'.$escaped_label.'</a>',
        ];
    }

    /**
     * Formats IP address and creates URL to more information
     */
    public static function ip(string $ip): string|false
    {
        if (! empty($ip)) {
            if (in_array($ip, self::IP['localhost']['addresses'])) {
                return self::IP['localhost']['name'];
            } else {
                return '<a href="'.
                    self::IP['locator'].
                    $ip.
                    '" target="_blank" rel="noopener">'.
                    $ip.
                    '</a>';
            }
        }

        return false;
    }

    /**
     * Reformats string to start with big first letter
     */
    public static function title_case(string $title): string
    {
        return mb_convert_case($title, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Converting number to specific format
     *
     * @param  int|float|numeric-string  $number
     */
    public static function number(int|float|string $number, bool $with_decimal = true, int $value = 1000000): string
    {
        if (empty($number)) {
            $converted = '';
        } else {
            if ($with_decimal) {
                $converted = number_format($number / $value, 1, '.', '');

                if ($converted < 1) {
                    $converted = substr($converted, 1, 2);
                }
            } else {
                $converted = number_format($number / $value, 0, '.', '');
            }
        }

        return $converted;
    }

    /**
     * Convert given data to readable format
     *
     * @deprecated Use debug() and decide where to emit the returned markup.
     */
    public static function pre(mixed $data, bool $to_print = true): void
    {
        if ($to_print) {
            echo self::debug($data);
        }
    }

    /**
     * Formats debug data without writing directly to the output buffer
     */
    public static function debug(mixed $data): string
    {
        return '<pre>'.htmlspecialchars(print_r($data, true), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</pre>';
    }

    /**
     * Converting string from Windows-1250 to UTF-8
     */
    public static function windows1250_to_utf8(string $string): string|false
    {
        return iconv(self::WINDOWS_1250, self::UTF_8, trim($string));
    }

    /**
     * Converting string from UTF-8 to Windows-1250
     */
    public static function utf8_to_windows1250(string $string): string|false
    {
        return iconv(self::UTF_8, self::WINDOWS_1250, trim($string));
    }

    /**
     * Formatting shortened string
     */
    public static function string(string $string, int $start = 0, int $length = 15): string
    {
        $string = strip_tags($string);
        $string_length = strlen($string);

        if ($string_length > $length) {
            $corrected = mb_substr($string, $start, $length).'...';
        } else {
            $corrected = $string;
        }

        return $corrected;
    }

    /**
     * Formats price for user
     */
    public static function price_format(int|float|string $price, int $decimal = 2): string|false
    {
        if (stripos(strval($price), ',') === false) {
            $price_format = number_format((float) $price, $decimal);
            $price_format = str_replace('.', '?', $price_format);
            $price_format = str_replace(',', '.', $price_format);
            $price_format = str_replace('?', ',', $price_format);

            return $price_format;
        }

        return false;
    }

    /**
     * Formats array to string
     *
     * @param  array<int, mixed>  $array
     *
     * @deprecated Use implode() directly.
     */
    public static function array_to_string(array $array, string $separator = '|'): string
    {
        return implode($separator, $array);
    }

    /**
     * Formats name and surname to one string
     *
     * @deprecated Use ordinary string concatenation or interpolation.
     */
    public static function fullname(string $name, string $surname, string $delimiter = ' '): string
    {
        return $name.$delimiter.$surname;
    }

    /**
     * Advanced database search
     *
     * @param  list<string>  $fields
     *
     * @deprecated Use search_clause() and bind the returned parameters.
     */
    public static function search_wizard(string $term, array $fields): string|false
    {
        if (empty($term) || empty($fields) || ! self::identifiers_are_valid($fields)) {
            return false;
        }

        $term_groups = [];

        foreach (preg_split('/\s+/', trim($term)) ?: [] as $term_item) {
            $escaped_term = self::escape_like($term_item);
            $field_clauses = array_map(
                static fn (string $field): string => $field." LIKE ('%".$escaped_term."%') ESCAPE '\\\\'",
                $fields
            );
            $term_groups[] = '( '.implode(' OR ', $field_clauses).' )';
        }

        return ' AND ( '.implode(' AND ', $term_groups).' ) ';
    }

    /**
     * Builds a parameterized SQL LIKE clause.
     *
     * @param  list<string>  $fields
     * @return array{sql: string, bindings: array<string, string>}|false
     */
    public static function search_clause(string $term, array $fields, string $parameter_prefix = 'search'): array|false
    {
        if (
            empty($term) ||
            empty($fields) ||
            ! self::identifiers_are_valid($fields) ||
            ! self::identifier_is_valid($parameter_prefix)
        ) {
            return false;
        }

        $bindings = [];
        $term_groups = [];

        foreach (preg_split('/\s+/', trim($term)) ?: [] as $term_index => $term_item) {
            $field_clauses = [];

            foreach ($fields as $field_index => $field) {
                $parameter = $parameter_prefix.'_'.$term_index.'_'.$field_index;
                $field_clauses[] = $field.' LIKE :'.$parameter." ESCAPE '\\\\'";
                $bindings[$parameter] = '%'.self::escape_like($term_item, false).'%';
            }

            $term_groups[] = '( '.implode(' OR ', $field_clauses).' )';
        }

        return [
            'sql' => ' AND ( '.implode(' AND ', $term_groups).' ) ',
            'bindings' => $bindings,
        ];
    }

    /**
     * Value for given language
     */
    public static function language_value(string $language, string $primary, string $secondary = ''): string
    {
        $secondary = empty($secondary) ? $primary : $secondary;

        return match ($language) {
            'serbian' => $secondary,
            default => $primary,
        };
    }

    /**
     * Prepares SQL statement
     *
     * @param  list<string>  $fields
     *
     * @deprecated Use in_clause() and bind the returned parameters.
     */
    public static function in_wizard(string $term, array $fields): string|false
    {
        if (empty($term) || empty($fields) || ! self::identifier_is_valid($term)) {
            return false;
        }

        $values = array_map(
            self::quote_sql_literal(...),
            $fields
        );

        return ' AND '.$term.' IN ('.implode(', ', $values).')';
    }

    /**
     * Builds a parameterized SQL IN clause.
     *
     * @param  list<scalar|null>  $values
     * @return array{sql: string, bindings: array<string, scalar|null>}|false
     */
    public static function in_clause(string $field, array $values, string $parameter_prefix = 'in'): array|false
    {
        if (
            empty($field) ||
            empty($values) ||
            ! self::identifier_is_valid($field) ||
            ! self::identifier_is_valid($parameter_prefix)
        ) {
            return false;
        }

        $placeholders = [];
        $bindings = [];

        foreach ($values as $index => $value) {
            $parameter = $parameter_prefix.'_'.$index;
            $placeholders[] = ':'.$parameter;
            $bindings[$parameter] = $value;
        }

        return [
            'sql' => ' AND '.$field.' IN ('.implode(', ', $placeholders).')',
            'bindings' => $bindings,
        ];
    }

    /**
     * Check a list of SQL identifiers
     *
     * @param  list<string>  $identifiers
     */
    private static function identifiers_are_valid(array $identifiers): bool
    {
        return array_all($identifiers, fn ($identifier) => self::identifier_is_valid($identifier));
    }

    /**
     * Allow ordinary and table-qualified SQL identifiers only
     */
    private static function identifier_is_valid(string $identifier): bool
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z_][a-zA-Z0-9_]*)*$/', $identifier) === 1;
    }

    /**
     * Escape wildcard characters in a LIKE value
     */
    private static function escape_like(string $value, bool $quote = true): string
    {
        $escaped = strtr($value, [
            '\\' => '\\\\',
            '%' => '\\%',
            '_' => '\\_',
            "'" => "''",
        ]);

        return $quote ? $escaped : str_replace("''", "'", $escaped);
    }

    /**
     * Quote a scalar for the backwards-compatible SQL helper
     */
    private static function quote_sql_literal(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return "'".str_replace("'", "''", (string) $value)."'";
    }
}
