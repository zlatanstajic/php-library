<?php

use PHP_Library\Core\Files\File;

beforeEach(function () {
    $this->dir = temp_dir('file');
});

afterEach(function () {
    remove_dir($this->dir);
});

it('writes to a new file', function () {
    $path = $this->dir.'new.txt';

    expect(File::write_to_file($path, 'hello'))->toBeInt()
        ->and(file_get_contents($path))->toBe('hello'.PHP_EOL);
});

it('appends by default', function () {
    $path = $this->dir.'a.txt';

    File::write_to_file($path, 'one');
    File::write_to_file($path, 'two');

    expect(file_get_contents($path))->toBe('one'.PHP_EOL.'two'.PHP_EOL);
});

it('prepends when told to', function () {
    $path = $this->dir.'b.txt';

    File::write_to_file($path, 'one');
    File::write_to_file($path, 'zero', false);

    expect(file_get_contents($path))->toBe('zero'.PHP_EOL.'one'.PHP_EOL);
});

it('writes into an existing empty file without a ValueError', function () {
    // fread() rejects a length of 0 on PHP 8; PHP 7 returned ''.
    $path = $this->dir.'empty.txt';
    touch($path);

    expect(File::write_to_file($path, 'hello'))->toBeInt()
        ->and(file_get_contents($path))->toBe('hello'.PHP_EOL);
});

it('reads back the last line', function () {
    $path = $this->dir.'c.txt';

    File::write_to_file($path, 'first');
    File::write_to_file($path, 'last');

    expect(File::read_from_file($path))->toBe('last');
});

it('returns false when reading a file that is not there', function () {
    expect(File::read_from_file($this->dir.'nope.txt'))->toBeFalse();
});

it('reads semicolon-separated contents into rows', function () {
    $path = $this->dir.'d.csv';
    file_put_contents($path, "a;b;c\nd;e;f\n");

    $result = File::read_file_contents($path);

    expect($result['status'])->toBeTrue()
        ->and($result['items'])->toHaveCount(2)
        ->and($result['items'][0][0])->toBe('a');
});

it('parses quoted separators and multiline CSV fields', function () {
    $path = $this->dir.'quoted.csv';
    file_put_contents($path, "name;note\nAda;\"one;two\"\nBob;\"line one\nline two\"\n");

    $result = File::read_csv($path);

    expect($result['status'])->toBeTrue()
        ->and($result['items'][1])->toBe(['Ada', 'one;two'])
        ->and($result['items'][2])->toBe(['Bob', "line one\nline two"]);
});

it('can unlink the file after reading it', function () {
    $path = $this->dir.'e.csv';
    file_put_contents($path, "a;b\n");

    File::read_file_contents($path, true);

    expect(file_exists($path))->toBeFalse();
});

it('reports status false for an empty location', function () {
    expect(File::read_file_contents(''))->toBe(['status' => false, 'items' => []]);
});

it('prepares download metadata without emitting output', function () {
    $path = $this->dir.'download.txt';
    file_put_contents($path, 'hello');

    ob_start();
    $download = File::prepare_download($path);

    expect(ob_get_clean())->toBe('')
        ->and($download['path'])->toBe($path)
        ->and($download['filename'])->toBe('download.txt')
        ->and($download['headers'])->toContain('Content-Length: 5')
        ->and(File::prepare_download($this->dir.'missing'))->toBeFalse();
});

it('falls back to the default image when the file is missing', function () {
    File::$image = [
        'location' => $this->dir,
        'default' => 'default.png',
    ];

    expect(File::image('missing.png'))->toBe($this->dir.'default.png');
});

it('falls back to the default image for an empty name', function () {
    File::$image = [
        'location' => $this->dir,
        'default' => 'default.png',
    ];

    expect(File::image(''))->toBe($this->dir.'default.png');
});

it('returns the real image when it exists', function () {
    File::$image = [
        'location' => $this->dir,
        'default' => 'default.png',
    ];
    write_png($this->dir.'real.png');

    expect(File::image('real.png'))->toBe($this->dir.'real.png');
});
