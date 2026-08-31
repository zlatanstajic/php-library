<?php

use PHP_Library\Core\Sites\Website;

beforeEach(function () {
    $_SERVER['HTTP_HOST'] = 'example.com';
    $_SERVER['PHP_SELF'] = '/app/index.php';
    $_SERVER['REQUEST_URI'] = '/app/index.php?q=1';
    $_SERVER['HTTP_REFERER'] = 'https://referer.test/';

    $this->dir = temp_dir('website');
    $this->icon = write_png($this->dir.'icon.png');

    $this->site = new Website([
        'name' => 'Test Site',
        'host' => 'https://example.com/',
        'made' => '2020',
        'language' => 'en',
        'charset' => 'utf-8',
        'description' => 'A description',
        'keywords' => 'a,b,c',
    ]);

    // Point images at a local file so nothing reaches the network.
    $this->site->add_to_images(['icon' => $this->icon, 'logo' => $this->icon]);
});

afterEach(function () {
    remove_dir($this->dir);
});

it('exposes the constructor values through getters', function () {
    expect($this->site->get_name())->toBe('Test Site')
        ->and($this->site->get_host())->toBe('https://example.com/')
        ->and($this->site->get_made())->toBe('2020')
        ->and($this->site->get_language())->toBe('en')
        ->and($this->site->get_charset())->toBe('utf-8')
        ->and($this->site->get_description())->toBe('A description')
        ->and($this->site->get_keywords())->toBe('a,b,c')
        ->and($this->site->has_errors())->toBeFalse();
});

it('records an error for each missing required parameter instead of throwing', function () {
    $bare = new Website([]);

    expect($bare->has_errors())->toBeTrue()
        ->and($bare->get_error())->toHaveCount(3)
        ->and(implode(' ', $bare->get_error()))->toContain('name')
        ->and(implode(' ', $bare->get_error()))->toContain('host')
        ->and(implode(' ', $bare->get_error()))->toContain('made');
});

it('returns null for unset optional values rather than erroring', function () {
    expect(new Website([])->get_name())->toBeNull();
});

it('falls back to defaults for the optional values', function () {
    $bare = new Website(['name' => 'n', 'host' => 'h', 'made' => '2020']);

    expect($bare->get_language())->toBe('EN')
        ->and($bare->get_charset())->toBe('UTF-8');
});

it('builds the server map from $_SERVER', function () {
    expect($this->site->get_server())->toMatchArray([
        'host' => 'example.com',
        'uri' => '/app/index.php?q=1',
        'page' => 'index.php',
    ]);
});

it('renders meta tags including the title', function () {
    expect($this->site->meta(['title' => 'Page Title']))->toContain('Page Title')
        ->and($this->site->meta())->toContain('A description');
});

it('renders modern escaped metadata without obsolete tags', function () {
    $meta = $this->site->meta([
        'title' => '<Dashboard>',
        'google_site_verification' => 'a"b',
    ]);

    expect($meta)->toContain('<meta charset="utf-8">')
        ->and($meta)->toContain('initial-scale=1')
        ->and($meta)->toContain('&lt;Dashboard&gt;')
        ->and($meta)->toContain('a&quot;b')
        ->and($meta)->not->toContain('maximum-scale')
        ->and($meta)->not->toContain('X-UA-Compatible')
        ->and($meta)->not->toContain('name="keywords"');
});

it('reports head and bottom as not loaded when empty', function () {
    expect($this->site->head())->toContain('NOT LOADED')
        ->and($this->site->bottom())->toContain('NOT LOADED');
});

it('renders stylesheet and script tags once added', function () {
    $this->site->add_to_head([
        [
            'type' => 'link',
            'path' => '/css/a.css',
        ],
    ]);
    $this->site->add_to_bottom([
        [
            'type' => 'script',
            'path' => '/js/a.js',
        ],
    ]);

    expect($this->site->head())->toContain('/css/a.css')
        ->and($this->site->head())->toContain('stylesheet')
        ->and($this->site->bottom())->toContain('/js/a.js');
});

it('looks up creator fields and reports false for unknown ones', function () {
    expect($this->site->creator('name'))->toBeString()
        ->and($this->site->creator('nope'))->toBeFalse();
});

it('looks up images and reports false for unknown ones', function () {
    expect($this->site->images('icon'))->toBe($this->icon)
        ->and($this->site->images('nope'))->toBeFalse();
});

it('measures a local image', function () {
    $size = $this->site->image_size($this->icon);

    expect($size['width'])->toBe(1)
        ->and($size['height'])->toBe(1)
        ->and($size['width_height'])->toBe('1x1');
});

it('renders a copyright signature spanning made year to now', function () {
    $signature = $this->site->signature();

    expect($signature)->toContain('Copyright')
        ->and($signature)->toContain('2020')
        ->and($signature)->toContain(date('Y'));
});

it('shows only the made year when asked', function () {
    expect($this->site->signature(true))->toContain('2020');
});

it('appends the licence notice on request', function () {
    expect($this->site->signature(false, true))->toContain('All Rights Reserved')
        ->and($this->site->signature())->not->toContain('All Rights Reserved');
});

it('renders a hidden signature in the site language', function () {
    expect($this->site->signature_hidden('EN'))->toContain('Proudly built by')
        ->and($this->site->signature_hidden('serbian'))->toContain('Ponosno izradio');
});

it('merges rather than replaces when told to', function () {
    $this->site->add_to_creator(['name' => 'Someone'], true);

    expect($this->site->creator('name'))->toBe('Someone')
        // 'website' survived the merge.
        ->and($this->site->creator('website'))->toBeString();
});

it('replaces wholesale by default', function () {
    $this->site->add_to_creator(['name' => 'Someone']);

    expect($this->site->creator('website'))->toBeFalse();
});
