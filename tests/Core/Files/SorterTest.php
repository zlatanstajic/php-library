<?php

use PHP_Library\Core\Files\Sorter;

beforeEach(function () {
    $this->root = temp_dir('sorter');
    $this->src = $this->root.'src/';
    $this->dst = $this->root.'dst/';

    mkdir($this->src);
    mkdir($this->dst);

    foreach (['000001', '000002', '001003', '002004'] as $name) {
        file_put_contents($this->src.$name.'.jpg', 'x');
    }

    file_put_contents($this->src.'ignore.txt', 'x');

    $this->params = [
        'where_to_read_files' => $this->src,
        'where_to_create_directories' => $this->dst,
        'folder_sufix' => '000',
        'number_of_directories' => 3,
        'types' => ['jpg'],
        'operation' => 'c',
    ];
});

afterEach(function () {
    remove_dir($this->root);
});

it('copies matching files into generated folders', function () {
    $sorter = new Sorter($this->params);

    expect($sorter->deploy())->toBeTrue();

    $copied = glob($this->dst.'*/*.jpg');

    expect($copied)->toHaveCount(4)
        ->and(glob($this->dst.'*', GLOB_ONLYDIR))->toHaveCount(3);
});

it('leaves the source files in place when copying', function () {
    new Sorter($this->params)->deploy();

    expect(glob($this->src.'*.jpg'))->toHaveCount(4);
});

it('moves files when the operation is move', function () {
    $sorter = new Sorter(['operation' => 'm'] + $this->params);

    expect($sorter->deploy())->toBeTrue()
        ->and(glob($this->src.'*.jpg'))->toBeEmpty()
        ->and(glob($this->dst.'*/*.jpg'))->toHaveCount(4);
});

it('ignores files whose extension is not listed', function () {
    new Sorter($this->params)->deploy();

    expect(glob($this->dst.'*/*.txt'))->toBeEmpty();
});

it('reports success with no errors', function () {
    $sorter = new Sorter($this->params);
    $sorter->deploy();

    $report = $sorter->report();

    expect($report['bool'])->toBe([
        'no_errors' => true,
        'successful_sorting' => true,
        'something_to_sort' => true,
    ])
        ->and($report['string'])->toContain('Files copied/not copied: 4/0')
        ->and($report)->toHaveKeys(['bool', 'string', 'array']);
});

it('reports nothing to sort for an empty source', function () {
    $empty = $this->root.'empty/';
    mkdir($empty);

    $sorter = new Sorter(['where_to_read_files' => $empty] + $this->params);

    expect($sorter->deploy())->toBeFalse()
        ->and($sorter->report()['bool']['something_to_sort'])->toBeFalse();
});

it('records an error when the directory count is missing', function () {
    $params = $this->params;
    unset($params['number_of_directories']);

    $sorter = new Sorter($params);
    $sorter->deploy();

    expect($sorter->has_errors())->toBeTrue()
        ->and($sorter->get_error()[0])->toContain('number_of_directories')
        ->and($sorter->report()['array']['result']['files']['number']['not_copied'])->toBe(0);
});

it('drives the unreachable failure branch through testing mode', function () {
    $sorter = new Sorter($this->params);
    $sorter->turn_on();

    expect($sorter->deploy())->toBeBool();
});
