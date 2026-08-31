<?php

use PHP_Library\Core\Data\User_Agent;

const CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36';
const FIREFOX = 'Mozilla/5.0 (X11; Linux x86_64) Gecko/20100101 Firefox/121.0';
const IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148';
const EDGE = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/128.0 Safari/537.36 Edg/128.0';
const OPERA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/128.0 Safari/537.36 OPR/113.0';
const ANDROID = 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 Chrome/128.0 Mobile Safari/537.36';

it('lists browsers, devices and crawlers', function () {
    expect(User_Agent::list_browsers())->toBeArray()->not->toBeEmpty()
        ->and(User_Agent::list_devices())->toBeArray()->not->toBeEmpty()
        ->and(User_Agent::list_crawlers())->toBeArray()->not->toBeEmpty();
});

it('lists operating systems by name, de-duplicated and sorted', function () {
    $names = User_Agent::list_operating_systems();

    expect($names)->toContain('Windows 10', 'Linux', 'Mac OS X')
        ->and(count($names))->toBe(count(array_unique($names)));
});

it('lists operating systems by group', function () {
    $groups = User_Agent::list_operating_systems(true);

    expect($groups)->toContain('Windows', 'Linux', 'iOS')
        ->and(count($groups))->toBeLessThan(count(User_Agent::list_operating_systems()));
});

it('detects the browser', function () {
    expect(User_Agent::detect_browser(CHROME))->toBe('Chrome')
        ->and(User_Agent::detect_browser(FIREFOX))->toBe('Firefox')
        ->and(User_Agent::detect_browser(EDGE))->toBe('Edge')
        ->and(User_Agent::detect_browser(OPERA))->toBe('Opera');
});

it('returns the fallback name when no browser matches', function () {
    expect(User_Agent::detect_browser('zzz', 'unknown'))->toBe('unknown')
        ->and(User_Agent::detect_browser('zzz'))->toBe('');
});

it('detects the operating system with its group', function () {
    $os = User_Agent::detect_operating_system(CHROME);

    expect($os['name'])->toBe('Windows 10')
        ->and($os['group'])->toBe('Windows');
});

it('does not confuse mobile operating systems with their compatibility tokens', function () {
    expect(User_Agent::detect_operating_system(IPHONE)['name'])->toBe('iPhone')
        ->and(User_Agent::detect_operating_system(ANDROID)['name'])->toBe('Android');
});

it('returns an empty shape with the fallback name for an unknown os', function () {
    expect(User_Agent::detect_operating_system('zzz', 'dunno'))
        ->toBe(['regex' => '', 'name' => 'dunno', 'group' => '']);
});

it('detects the device', function () {
    expect(User_Agent::detect_device(CHROME))->toBe('Windows')
        ->and(User_Agent::detect_device(IPHONE))->toBe('iPhone');
});

it('spots a mobile user agent', function () {
    expect(User_Agent::is_mobile(IPHONE))->toBeTrue()
        ->and(User_Agent::is_mobile(CHROME))->toBeFalse();
});

it('spots a crawler by exact user agent', function () {
    $known = User_Agent::list_crawlers()[0];

    expect(User_Agent::is_crawler($known))->toBeTrue()
        ->and(User_Agent::is_crawler(CHROME))->toBeFalse();
});

it('matches signatures at offset zero and recognises crawler families', function () {
    expect(User_Agent::detect_browser('Chrome/120', 'no-match'))->toBe('Chrome')
        ->and(User_Agent::detect_device('iPhone'))->toBe('iPhone')
        ->and(User_Agent::is_crawler('Googlebot/2.1 (+http://www.google.com/bot.html)'))->toBeTrue();
});
