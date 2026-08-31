<?php

use PHP_Library\Core\Arrangements\Date_Time_Format as DTF;

it('exposes the format catalogue as a public property', function () {
    expect(DTF::$types)->toHaveKeys(['user', 'database', 'friendly', 'unfriendly'])
        ->and(DTF::$types['database']['format'])->toBe('Y-m-d');
});

it('returns the current timestamp in the unfriendly format by default', function () {
    expect(DTF::current())->toMatch('/^\d{14}$/');
});

it('honours an explicit format', function () {
    expect(DTF::current('Y'))->toBe(date('Y'));
});

it('compares a past date as before today and a future one as not', function () {
    expect(DTF::compare('1999-01-01'))->toBeTrue()
        ->and(DTF::compare(date('Y-m-d', strtotime('+10 days'))))->toBeFalse();
});

it('formats a date with and without time', function () {
    expect(DTF::format('2020-03-05 14:30:00'))->toBe('05-Mar-2020 14:30:00')
        ->and(DTF::format('2020-03-05 14:30:00', true))->toBe('05-Mar-2020');
});

it('converts to database format', function () {
    expect(DTF::format_to_database('05.03.2020'))->toBe('2020-03-05');
});

it('converts to user format', function () {
    expect(DTF::format_to_user('2020-03-05'))->toBe('05.03.2020');
});

it('treats the sentinel dates as empty', function (string $date) {
    expect(DTF::format_to_database($date))->toBeFalse()
        ->and(DTF::format_to_user($date))->toBeFalse();
})->with(['01.01.1970', '1970-01-01', '0000-00-00', '']);

it('converts minutes to hours', function (int $minutes, string $expected) {
    expect(DTF::minutes_to_hours($minutes))->toBe($expected);
})->with([[0, '00:00'], [59, '00:59'], [60, '01:00'], [90, '01:30'], [1440, '24:00']]);

it('accepts a custom hour format', function () {
    expect(DTF::minutes_to_hours(90, '%dh%02d'))->toBe('1h30');
});

it('converts hours to minutes', function (string $time, int $expected) {
    expect(DTF::hours_to_minutes($time))->toBe($expected);
})->with([['00:00', 0], ['01:30', 90], ['24:00', 1440], ['no-colon', 0]]);

it('names days, serbian by default', function () {
    expect(DTF::number_to_day(1))->toBe('ponedeljak')
        ->and(DTF::number_to_day(7))->toBe('nedelja');
});

it('names days in english on request', function () {
    expect(DTF::number_to_day(1, 'english'))->toBe('monday')
        ->and(DTF::number_to_day(1, 'english', false))->toBe('Monday');
});

it('rejects out-of-range day numbers', function (int $day) {
    expect(DTF::number_to_day($day))->toBeFalse();
})->with([0, 8, -1]);

it('names months, serbian by default', function () {
    expect(DTF::number_to_month(1))->toBe('januar')
        ->and(DTF::number_to_month(12, 'english', false))->toBe('December');
});

it('rejects out-of-range month numbers', function (int $month) {
    expect(DTF::number_to_month($month))->toBeFalse();
})->with([0, 13]);

it('does not warn on a fractional numeric string', function () {
    // Previously triggered "Implicit conversion from float ... loses precision".
    expect(DTF::number_to_day('3.7'))->toBe('sreda')
        ->and(DTF::number_to_month('5.5'))->toBe('maj');
});

it('prefixes a string with a timestamp', function () {
    expect(DTF::prefix('file'))->toMatch('/^\d{14}_file$/')
        ->and(DTF::prefix(''))->toBeFalse();
});

it('reads a date out of a JMBG', function () {
    expect(DTF::date_from_jmbg('0101990710006'))->toBe('1. 1. 1990.');
});

it('rejects a JMBG of the wrong length', function () {
    expect(DTF::date_from_jmbg('123'))->toBeFalse();
});

it('names the first day of a known year', function () {
    expect(DTF::first_day_of_year('l', 2020))->toBe('Wednesday');
});

it('rejects an unsupported first-day format', function () {
    expect(DTF::first_day_of_year('Y', 2020))->toBeFalse();
});

it('walks backwards and forwards by days', function () {
    expect(DTF::days_before(1))->toBe(date('Y-m-d', strtotime('-1 day')))
        ->and(DTF::days_after(1))->toBe(date('Y-m-d', strtotime('+1 day')))
        ->and(DTF::days_before(0))->toBeFalse()
        ->and(DTF::days_after(0))->toBeFalse();
});

it('lists days with sunday first by default', function () {
    $days = DTF::get_days();

    expect($days['php'][0])->toBe('Sunday')
        ->and($days['php'])->toHaveCount(7)
        ->and(json_decode($days['json'], true))->toBe($days['php']);
});

it('lists days with monday first on request', function () {
    expect(DTF::get_days('', 0, false)['php'][0])->toBe('Monday');
});

it('shortens day names to a given length', function () {
    expect(DTF::get_days('english', 3)['php'][0])->toBe('Sun');
});

it('lists serbian days and months', function () {
    expect(DTF::get_days('serbian')['php'][0])->toBe('Nedelja')
        ->and(DTF::get_months('serbian')['php'][0])->toBe('Januar');
});

it('lists twelve months and shortens them', function () {
    expect(DTF::get_months()['php'])->toHaveCount(12)
        ->and(DTF::get_months('english', 3)['php'][0])->toBe('Jan');
});

it('falls back to english for an unknown language', function () {
    expect(DTF::get_days('klingon')['php'][0])->toBe('Sunday');
});
