<?php

use PHP_Library\Core\Services\Web_Service;

it('starts clean with no url', function () {
    $service = new Web_Service;

    expect($service->has_errors())->toBeFalse();
});

it('refuses an empty url and records an error rather than throwing', function () {
    $service = new Web_Service;
    $service->set_url('');

    expect($service->has_errors())->toBeTrue()
        ->and($service->get_error())->toContain('Please set URL');
});

it('accepts a url without complaint', function () {
    $service = new Web_Service;
    $service->set_url('https://example.com');

    expect($service->has_errors())->toBeFalse();
});

it('returns false when no url has been set', function () {
    expect((new Web_Service)->response())->toBeFalse();
});

it('drives the cURL-unavailable branch through testing mode', function () {
    // is_function_available() reports FALSE under testing, then pops the error,
    // so the failure path is exercised without disabling the extension.
    $service = new Web_Service('https://example.invalid');
    $service->turn_on();

    $service->response();

    expect($service->has_errors())->toBeFalse();
});

it('shapes a response as status, code and response', function () {
    // example.invalid never resolves, so this stays offline and fails fast.
    $service = new Web_Service('https://example.invalid');
    $response = $service->response();

    expect($response)->toBeArray()->toHaveKeys(['status', 'code', 'response'])
        ->and($response['status'])->toBeBool();
});

it('reports a non-200 code as unavailable', function () {
    $response = new Web_Service('https://example.invalid')->response();

    expect($response['status'])->toBeFalse();
});

it('reaches a live endpoint', function () {
    $response = new Web_Service('https://example.com')->response();

    expect($response['code'])->toBe(200)
        ->and($response['status'])->toBeTrue();
})->skip(! getenv('PHPLIB_NETWORK'), 'set PHPLIB_NETWORK=1 to run live HTTP tests');
