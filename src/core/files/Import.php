<?php

/**
 * Import
 *
 * Import data from file using customisation of PHPOffice/PhpSpreadsheet
 * Location: https://github.com/PHPOffice/PhpSpreadsheet
 *
 * @author       Ivan Skokić <iskokic@gmail.com>
 */

namespace PHP_Library\Core\Files;

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Import data from file using customisation of PHPOffice/PhpSpreadsheet
 */
class Import
{
    /**
     * Available types
     *
     * @var list<string>
     */
    private const array ALLOWED_TYPES = [
        'xlsx',
        'xls',
        'csv',
    ];

    /**
     * Allowed types file for import
     *
     * @return list<string>
     */
    public static function allowed_types(): array
    {
        return self::ALLOWED_TYPES;
    }

    /**
     * Import data from file
     *
     * @return array<int, array<string, mixed>>|false
     */
    public static function import_data(string $file_path): array|false
    {
        if (file_exists($file_path)) {
            $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);

            if (in_array(strtolower($file_extension), self::ALLOWED_TYPES)) {
                $spreadsheet = IOFactory::load($file_path);
                $sheetData = $spreadsheet->getActiveSheet()->toArray(
                    null,
                    true,
                    true,
                    true
                );

                return $sheetData;
            }
        }

        return false;
    }
}
