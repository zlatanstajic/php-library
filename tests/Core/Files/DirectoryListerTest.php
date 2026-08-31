<?php

use PHP_Library\Core\Files\Directory_Lister;

beforeEach(function () {
    $this->dir = temp_dir('lister');

    mkdir($this->dir.'sub');
    file_put_contents($this->dir.'one.php', '<?php');
    file_put_contents($this->dir.'two.txt', 'text');
    file_put_contents($this->dir.'sub/three.php', '<?php');
});

afterEach(function () {
    remove_dir($this->dir);
});

it('crawls recursively and finds nested files', function () {
    $result = Directory_Lister::listing([
        'directory' => $this->dir,
        'method' => 'crawl',
        'print' => false,
        'types' => ['php'],
    ]);

    $titles = array_column($result['listing'], 'title');

    expect($titles)->toContain('one', 'three');
});

it('filters by extension while crawling', function () {
    $result = Directory_Lister::listing([
        'directory' => $this->dir,
        'method' => 'crawl',
        'print' => false,
        'types' => ['txt'],
    ]);

    $titles = array_column($result['listing'], 'title');

    expect($titles)->toContain('two')->and($titles)->not->toContain('one');
});

it('lists folders', function () {
    $result = Directory_Lister::listing([
        'directory' => $this->dir,
        'method' => 'folders',
        'print' => false,
    ]);

    expect($result)->toBeArray()->toHaveKey('listing');
});

it('reports counts alongside the listing', function () {
    $result = Directory_Lister::listing([
        'directory' => $this->dir,
        'method' => 'crawl',
        'print' => false,
        'types' => ['php'],
    ]);

    expect($result)->toHaveKey('listing')
        ->and($result['listing'])->not->toBeEmpty();
});

it('lists only top-level files with the files method', function () {
    $result = Directory_Lister::listing([
        'directory' => $this->dir,
        'method' => 'files',
        'print' => false,
        'types' => ['php'],
    ]);

    $titles = array_column($result['listing'], 'title');

    expect($result)->toHaveKeys(['listing', 'count', 'max'])
        // "three" lives in a subdirectory, so it is not picked up here.
        ->and($titles)->toContain('one')->and($titles)->not->toContain('three');
});

it('returns filesystem data without embedded presentation markup', function () {
    ob_start();
    $result = Directory_Lister::listing([
        'directory' => $this->dir,
        'method' => 'files',
        'print' => true,
        'types' => ['php'],
    ]);

    expect(ob_get_clean())->toBe('')
        ->and($result['listing'][0])->not->toHaveKey('open');
});

it('returns false when no top-level file matches', function () {
    $result = Directory_Lister::listing([
        'directory' => $this->dir,
        'method' => 'files',
        'print' => false,
        'types' => ['nomatch'],
    ]);

    expect($result)->toBeFalse();
});
