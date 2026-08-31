<?php

use PHP_Library\Core\Files\Import;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

beforeEach(function () {
    $this->dir = temp_dir('import');
});

afterEach(function () {
    remove_dir($this->dir);
});

/**
 * Fixtures are written at run time by PhpSpreadsheet itself - no binaries live
 * in the repository.
 */
function write_sheet(string $path, string $writer): string
{
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'Name');
    $sheet->setCellValue('B1', 'Qty');
    $sheet->setCellValue('A2', 'Widget');
    $sheet->setCellValue('B2', 42);

    IOFactory::createWriter($spreadsheet, $writer)->save($path);

    return $path;
}

it('lists the allowed types', function () {
    expect(Import::allowed_types())->toBe(['xlsx', 'xls', 'csv']);
});

it('round-trips an xlsx file', function () {
    $rows = Import::import_data(write_sheet($this->dir.'book.xlsx', 'Xlsx'));

    expect($rows)->toBeArray()
        ->and($rows[1]['A'])->toBe('Name')
        ->and($rows[2]['A'])->toBe('Widget')
        // PhpSpreadsheet hands numeric cells back as strings from toArray().
        ->and($rows[2]['B'])->toEqual(42);
});

it('round-trips a csv file', function () {
    $rows = Import::import_data(write_sheet($this->dir.'book.csv', 'Csv'));

    expect($rows[1]['A'])->toBe('Name')
        ->and($rows[2]['A'])->toBe('Widget');
});

it('returns false for a file that is not there', function () {
    expect(Import::import_data($this->dir.'missing.xlsx'))->toBeFalse();
});

it('returns false for a disallowed extension', function () {
    $path = $this->dir.'notes.md';
    file_put_contents($path, '# hi');

    expect(Import::import_data($path))->toBeFalse();
});

it('accepts an uppercase extension', function () {
    $path = write_sheet($this->dir.'BOOK.XLSX', 'Xlsx');

    expect(Import::import_data($path))->toBeArray();
});
