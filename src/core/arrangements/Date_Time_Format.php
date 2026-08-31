<?php

/**
 * Date_Time_Format
 *
 * Date and Time formatting, validating, comparing, converting...
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\Arrangements;

/**
 * Date and Time formatting, validating, comparing, converting...
 */
class Date_Time_Format
{
    /**
     * Date and time types
     *
     * Public, and therefore part of the API - kept as a static property rather
     * than promoted to a constant so that Date_Time_Format::$types keeps working.
     *
     * @var array<string, array<string, string>>
     */
    public static array $types = [
        'user' => [
            'format' => 'd.m.Y',
            'placeholder' => 'DD.MM.YYYY',
            'regex' => '^([0-9]{2})\.([0-9]{2})\.([0-9]{4})$^',
        ],
        'database' => [
            'format' => 'Y-m-d',
            'placeholder' => 'YYYY-MM-DD',
            'regex' => '^([0-9]{4})-([0-9]{2})-([0-9]{2})$^',
        ],
        'friendly' => [
            'date' => 'd-M-Y',
            'datetime' => 'd-M-Y H:i:s',
        ],
        'unfriendly' => [
            'date' => 'Ymd',
            'datetime' => 'YmdHis',
        ],
    ];

    /**
     * Days in week divided by languages
     *
     * @var array<string, list<string>>
     */
    private const array DAYS = [
        'english' => [
            'Sunday',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
        ],
        'serbian' => [
            'Nedelja',
            'Ponedeljak',
            'Utorak',
            'Sreda',
            'Četvrtak',
            'Petak',
            'Subota',
        ],
    ];

    /**
     * Months in year divided by languages
     *
     * @var array<string, list<string>>
     */
    private const array MONTHS = [
        'english' => [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ],
        'serbian' => [
            'Januar',
            'Februar',
            'Mart',
            'April',
            'Maj',
            'Jun',
            'Jul',
            'Avgust',
            'Septembar',
            'Oktobar',
            'Novembar',
            'Decembar',
        ],
    ];

    /**
     * Invalid dates for database insertion
     *
     * @var list<string>
     */
    private const array INVALID_DATES = [
        '01.01.1970',
        '1970-01-01',
        '0000-00-00',
    ];

    /**
     * Returns current date-time of given format
     */
    public static function current(string $format = ''): string
    {
        if (empty($format)) {
            $format = self::$types['unfriendly']['datetime'];
        }

        return date($format);
    }

    /**
     * Compares given date with current date
     */
    public static function compare(string $date): bool
    {
        return self::current(self::$types['database']['format']) >
            date(self::$types['database']['format'], intval(strtotime($date)));
    }

    /**
     * Formats date to friendly format with or without time
     */
    public static function format(string $date, bool $without_time = false): string
    {
        $type = $without_time
            ? self::$types['friendly']['date']
            : self::$types['friendly']['datetime'];

        return date($type, intval(strtotime($date)));
    }

    /**
     * Formats date to database-friendly format
     */
    public static function format_to_database(string $date): string|false
    {
        if (self::not_empty($date)) {
            $format_to_database = date(
                self::$types['database']['format'],
                intval(strtotime($date))
            );

            return $format_to_database;
        }

        return false;
    }

    /**
     * Formats date to user-friendly format
     */
    public static function format_to_user(string $date): string|false
    {
        if (self::not_empty($date)) {
            $format_to_user = date(
                self::$types['user']['format'],
                intval(strtotime($date))
            );

            return $format_to_user;
        }

        return false;
    }

    /**
     * Checking if given date is considered as not empty
     */
    private static function not_empty(string $date): bool
    {
        if (empty($date) || in_array($date, self::INVALID_DATES)) {
            return false;
        }

        return true;
    }

    /**
     * Converts minutes to hours
     *
     * @param  int|numeric-string  $time
     */
    public static function minutes_to_hours(int|string $time = 0, string $format = '%02d:%02d'): string
    {
        if (intval($time) > 0) {
            $hours = floor($time / 60);
            $minutes = ($time % 60);

            return sprintf($format, $hours, $minutes);
        } else {
            return '00:00';
        }
    }

    /**
     * Converts hours to minutes
     */
    public static function hours_to_minutes(string $time): int
    {
        $minutes = 0;

        if (str_contains($time, ':')) {
            $exploded = explode(':', $time);

            $hours = $minutes = 0;

            $hours_first = true;

            foreach ($exploded as $row) {
                $number = $row;

                if ($hours_first) {
                    $hours = $number;

                    $hours_first = false;
                } else {
                    $minutes = $number;
                }
            }

            $hours_to_minutes = intval($hours) * 60;
            $minutes = $hours_to_minutes + intval($minutes);
        }

        return $minutes;
    }

    /**
     * Converts number of day to day name for given language
     *
     * @param  int|numeric-string  $day
     */
    public static function number_to_day(int|string $day, string $language = '', bool $lowercase = true): string|false
    {
        if ($day >= 1 && $day <= 7) {
            if (empty($language)) {
                $language = 'serbian';
            }

            $day = self::get_days($language, 0, false)['php'][intval($day) - 1];

            return $lowercase ? strtolower($day) : $day;
        }

        return false;
    }

    /**
     * Converts number of month to month name for given language
     *
     * @param  int|numeric-string  $month
     */
    public static function number_to_month(int|string $month, string $language = '', bool $lowercase = true): string|false
    {
        if ($month >= 1 && $month <= 12) {
            if (empty($language)) {
                $language = 'serbian';
            }

            $month = self::get_months($language)['php'][intval($month) - 1];

            return $lowercase ? strtolower($month) : $month;
        }

        return false;
    }

    /**
     * Adds date-time prefix to given string
     */
    public static function prefix(string $string): string|false
    {
        if (! empty($string)) {
            return date(self::$types['unfriendly']['datetime']).'_'.$string;
        }

        return false;
    }

    /**
     * Format JMBG to date
     */
    public static function date_from_jmbg(string $jmbg): string|false
    {
        if (strlen($jmbg) === 13) {
            $date_day = substr($jmbg, 0, 2);
            $date_month = substr($jmbg, 2, 2);
            $date_year = substr($jmbg, 4, 3);

            if (substr($date_day, 0, 1) == 0) {
                $date_day = substr($date_day, 1, 2);
            }

            if (substr($date_month, 0, 1) == 0) {
                $date_month = substr($date_month, 1, 2);
            }

            $number_year = $date_year > 100 ? 1 : 2;

            return $date_day.
                '. '.
                $date_month.
                '. '.
                $number_year.
                $date_year.
                '.';
        }

        return false;
    }

    /**
     * Name of the first day in year for given format
     */
    public static function first_day_of_year(string $format = 'l', int|string $year = 0): string|false
    {
        if (in_array($format, ['d', 'D', 'j', 'l', 'N', 'S', 'z'])) {
            if (empty($year)) {
                $year = date('Y');
            }

            return date($format, intval(strtotime('01.01.'.$year)));
        }

        return false;
    }

    /**
     * Date before certain number of days
     */
    public static function days_before(int|string $number_of_days, string $format = ''): string|false
    {
        if (! empty($number_of_days)) {
            if (empty($format)) {
                $format = self::$types['database']['format'];
            }

            return date($format, intval(strtotime(' -'.$number_of_days.' day')));
        }

        return false;
    }

    /**
     * Date after certain number of days
     */
    public static function days_after(int|string $number_of_days, string $format = ''): string|false
    {
        if (! empty($number_of_days)) {
            if (empty($format)) {
                $format = self::$types['database']['format'];
            }

            return date($format, intval(strtotime(' +'.$number_of_days.' day')));
        }

        return false;
    }

    /**
     * Get list of days
     *
     * @return array{php: list<string>, json: string|false}
     */
    public static function get_days(string $lang = '', int $length = 0, bool $sunday_first = true): array
    {
        $days = self::DAYS[$lang] ?? self::DAYS['english'];

        if (! empty($length)) {
            $days_short = [];

            foreach ($days as $item) {
                $days_short[] = substr($item, 0, $length);
            }

            $days = $days_short;
        }

        if (! $sunday_first) {
            $days = array_merge(
                array_slice($days, 1),
                [$days[0]]
            );
        }

        return [
            'php' => $days,
            'json' => json_encode($days),
        ];
    }

    /**
     * Get list of months
     *
     * @return array{php: list<string>, json: string|false}
     */
    public static function get_months(string $lang = '', int $length = 0): array
    {
        $months = self::MONTHS[$lang] ?? self::MONTHS['english'];

        if (! empty($length)) {
            $months_short = [];

            foreach ($months as $item) {
                $months_short[] = substr($item, 0, $length);
            }

            $months = $months_short;
        }

        return [
            'php' => $months,
            'json' => json_encode($months),
        ];
    }
}
