<?php

use PHP_Library\Core\Files\Export;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

it('lists the allowed types', function () {
    expect(Export::allowed_types())->toBe(['xlsx', 'xls', 'csv', 'osp']);
});

it('builds a spreadsheet without emitting anything', function () {
    // export_file() sends headers and exits, so build() is the testable seam.
    $spreadsheet = Export::build([
        'type' => 'xlsx',
        'head' => [
            'Name',
            'Qty',
        ],
        'data' => [
            [
                'Widget',
                42,
            ],
        ],
    ]);

    expect($spreadsheet)->toBeInstanceOf(Spreadsheet::class);
});

it('writes the head row and the data rows', function () {
    $sheet = Export::build([
        'type' => 'xlsx',
        'head' => [
            'Name',
            'Qty',
        ],
        'data' => [
            [
                'Widget',
                42,
            ],
            [
                'Gadget',
                7,
            ],
        ],
    ])->getActiveSheet();

    expect($sheet->getCell('A1')->getValue())->toBe('Name')
        ->and($sheet->getCell('B1')->getValue())->toBe('Qty')
        ->and($sheet->getCell('A2')->getValue())->toBe('Widget')
        ->and($sheet->getCell('B2')->getValue())->toBe(42)
        ->and($sheet->getCell('A3')->getValue())->toBe('Gadget');
});

it('applies document properties', function () {
    $properties = Export::build([
        'type' => 'xlsx',
        'head' => ['A'],
        'data' => [['1']],
        'document_properties' => [
            'creator' => 'Zlatan',
            'title' => 'Report',
            'description' => 'Desc',
            'keywords' => 'k',
            'category' => 'c',
        ],
    ])->getProperties();

    expect($properties->getCreator())->toBe('Zlatan')
        ->and($properties->getTitle())->toBe('Report')
        ->and($properties->getDescription())->toBe('Desc');
});

it('keeps the built-in document properties when none are given', function () {
    $properties = Export::build(['type' => 'xlsx', 'head' => ['A'], 'data' => [['1']]])->getProperties();

    expect($properties->getCreator())->toBeString()->not->toBeEmpty();
});

it('forces TEXT columns to be stored as strings', function () {
    $sheet = Export::build([
        'type' => 'xlsx',
        'head' => ['Code'],
        'data' => [['0042']],
        'data_types' => [
            [
                'index' => 0,
                'type' => 'TEXT',
            ],
        ],
    ])->getActiveSheet();

    expect($sheet->getCell('A2')->getValue())->toBe('0042');
});

it('still returns a spreadsheet for the xls type', function () {
    $sheet = Export::build([
        'type' => 'xls',
        'head' => ['Name'],
        'data' => [['Widget']],
    ])->getActiveSheet();

    expect($sheet->getCell('A2')->getValue())->toBe('Widget');
});

it('leaves the sheet empty when there is no data', function () {
    $sheet = Export::build(['type' => 'xlsx', 'head' => ['Name'], 'data' => []])->getActiveSheet();

    expect($sheet->getCell('A1')->getValue())->toBeNull();
});

it('saves a spreadsheet without emitting or terminating', function () {
    $directory = temp_dir('export-save');
    $path = $directory.'report.xlsx';

    try {
        expect(Export::save([
            'type' => 'xlsx',
            'head' => ['Name'],
            'data' => [['Widget']],
        ], $path))->toBeTrue()
            ->and(is_file($path))->toBeTrue()
            ->and(filesize($path))->toBeGreaterThan(0);
    } finally {
        remove_dir($directory);
    }
});

it('saves delimited exports directly', function () {
    $directory = temp_dir('export-csv');
    $path = $directory.'report.csv';

    try {
        expect(Export::save([
            'type' => 'csv',
            'data' => [['Widget', 42]],
        ], $path))->toBeTrue()
            ->and(file_get_contents($path))->toBe("Widget;42\r\n");
    } finally {
        remove_dir($directory);
    }
});

it('saves osp exports as delimited text', function () {
    $directory = temp_dir('export-osp');
    $path = $directory.'report.osp';

    try {
        expect(Export::save([
            'type' => 'osp',
            'data' => [['A;B', 'quoted']],
        ], $path))->toBeTrue()
            ->and(file_get_contents($path))->toBe("\"A;B\";quoted\r\n");
    } finally {
        remove_dir($directory);
    }
});
