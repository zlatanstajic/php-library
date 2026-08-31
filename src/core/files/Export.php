<?php

/**
 * Export
 *
 * Export files using customisation of PHPOffice/PhpSpreadsheet
 * Location: https://github.com/PHPOffice/PhpSpreadsheet
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\Files;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Export files using customisation of PHPOffice/PhpSpreadsheet
 */
class Export
{
    /**
     * Instance of Spreadsheet object
     */
    private static ?Spreadsheet $spreadsheet = null;

    /**
     * Properties for file export
     *
     * @var array<string, mixed>
     */
    private static array $properties = [
        'file_name' => 'file_export',
        'type' => 'xlsx',
        'head' => [],
        'data' => [],
        'data_types' => [],
        'document_properties' => [
            'creator' => 'Maarten Balliauw',
            'title' => 'Office 2007 XLSX Test Document',
            'description' => 'Test document for Office 2007 XLSX, generated using PHP classes.',
            'keywords' => 'office 2007 openxml php',
            'category' => 'Test result file',
        ],
    ];

    /**
     * Available cells
     *
     * @var list<string>
     */
    private const array CELLS = [
        '',
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
    ];

    /**
     * Available types
     *
     * @var list<string>
     */
    private const array ALLOWED_TYPES = [
        'xlsx',
        'xls',
        'csv',
        'osp',
    ];

    /**
     * Output profile per export type
     *
     * @var array<string, array<string, mixed>>
     */
    private const array OUTPUT_PROFILES = [
        'osp' => [
            'content_type' => 'text/txt',
            'file_extension' => 'osp',
            'writer_extension' => 'Csv',
            'to_flush_ob' => true,
        ],
        'csv' => [
            'content_type' => 'text/csv',
            'file_extension' => 'csv',
            'writer_extension' => 'Csv',
            'to_flush_ob' => true,
        ],
        'xls' => [
            'content_type' => 'application/vnd.ms-excel',
            'file_extension' => 'xls',
            'writer_extension' => 'Xls',
        ],
        'xlsx' => [
            'content_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'file_extension' => 'xlsx',
            'writer_extension' => 'Xlsx',
        ],
    ];

    /**
     * Export file
     *
     * Sends the spreadsheet to the browser and terminates the request.
     *
     * @deprecated Use build() or save() and let the application emit a response.
     */
    public static function export_file(array $params): void
    {
        $output = self::build_internal($params);

        if ($output !== false) {
            self::output($output);
        }
    }

    /**
     * Build the populated Spreadsheet without emitting anything
     *
     * Same work as export_file() minus the headers, the writer and the exit,
     * so the result can be inspected in-process.
     */
    public static function build(array $params): ?Spreadsheet
    {
        self::build_internal($params);

        return self::$spreadsheet;
    }

    /**
     * Save an export without sending headers or terminating the request
     */
    public static function save(array $params, string $path): bool
    {
        $output = self::build_internal($params);

        if ($output === false || self::$spreadsheet === null) {
            return false;
        }

        if (isset($output['print_data']) && is_string($output['print_data'])) {
            return file_put_contents($path, $output['print_data']) !== false;
        }

        $writer = IOFactory::createWriter(self::$spreadsheet, $output['writer_extension']);
        $writer->save($path);

        return true;
    }

    /**
     * Apply properties, create the Spreadsheet and arrange the data
     *
     * @return array<string, mixed>|false output profile, or FALSE for an unknown type
     */
    private static function build_internal(array $params): array|false
    {
        self::set_properties($params);
        self::create_spreadsheet_object();

        return self::arrange();
    }

    /**
     * Setting properties for file export
     */
    private static function set_properties(array $params): void
    {
        foreach (['head', 'data', 'type', 'data_types', 'file_name'] as $key) {
            if (isset($params[$key])) {
                self::$properties[$key] = $params[$key];
            }
        }

        if (isset($params['document_properties'])) {
            self::set_document_properties($params['document_properties']);
        }
    }

    /**
     * Create Spreadsheet object
     */
    private static function create_spreadsheet_object(): void
    {
        // Create new Spreadsheet object
        self::$spreadsheet = new Spreadsheet;

        // Set document properties
        self::$spreadsheet->getProperties()->setCreator(self::$properties['document_properties']['creator']);
        self::$spreadsheet->getProperties()->setLastModifiedBy(self::$properties['document_properties']['creator']);
        self::$spreadsheet->getProperties()->setTitle(self::$properties['document_properties']['title']);
        self::$spreadsheet->getProperties()->setSubject(self::$properties['document_properties']['title']);
        self::$spreadsheet->getProperties()->setDescription(self::$properties['document_properties']['description']);
        self::$spreadsheet->getProperties()->setKeywords(self::$properties['document_properties']['keywords']);
        self::$spreadsheet->getProperties()->setCategory(self::$properties['document_properties']['category']);
    }

    /**
     * Setting export document_properties
     */
    private static function set_document_properties(array $params): void
    {
        $keys = array_keys(self::$properties['document_properties']);

        foreach ($keys as $key) {
            if (isset($params[$key])) {
                self::$properties['document_properties'][$key] = $params[$key];
            }
        }
    }

    /**
     * Arrange the data for the configured type
     *
     * @return array<string, mixed>|false output profile, or FALSE for an unknown type
     */
    private static function arrange(): array|false
    {
        $type = self::$properties['type'];

        if (! array_key_exists((string) $type, self::OUTPUT_PROFILES)) {
            return false;
        }

        $output = self::OUTPUT_PROFILES[$type];

        if ($type === 'osp' || $type === 'csv') {
            $output['print_data'] = self::line_arrangement(self::$properties['data']);
        } else {
            self::cell_arrangement(
                self::$spreadsheet,
                self::$properties['head'],
                self::$properties['data'],
                self::$properties['data_types']
            );
        }

        return $output;
    }

    /**
     * Output for given parameters
     */
    private static function output(array $params): void
    {
        $file_name = self::$properties['file_name'];
        $spreadsheet = self::$spreadsheet;

        $content_type = $params['content_type'];
        $file_extension = $params['file_extension'];
        $writer_extension = $params['writer_extension'];

        $to_flush_ob = $params['to_flush_ob'] ?? false;

        $print_data = $params['print_data'] ?? false;

        if (! headers_sent()) {
            header('Content-Type: '.$content_type);
            header('Content-Disposition: attachment;filename="'.$file_name.'.'.$file_extension.'"');
            header('Cache-Control: max-age=0');

            if ($to_flush_ob) {
                ob_end_flush();
            }

            if (empty($print_data)) {
                $writer = IOFactory::createWriter($spreadsheet, $writer_extension);
                $writer->save('php://output');
            } else {
                echo $print_data;
            }

            exit;
        }
    }

    /**
     * Arrange data line by line for plain-text exports
     */
    private static function line_arrangement(array $data): string
    {
        $stream = fopen('php://temp', 'w+');

        if ($stream === false) {
            return '';
        }

        foreach ($data as $item) {
            fputcsv($stream, array_values($item), ';', '"', '', "\r\n");
        }

        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        return $contents;
    }

    private static function cell_arrangement(
        Spreadsheet $spreadsheet,
        array $head,
        array $data,
        array $data_types = []
    ): bool {
        if (! empty($data)) {
            // Print head
            $iteration = 1;

            foreach ($head as $item) {
                $spreadsheet->getActiveSheet()->getColumnDimension(self::CELLS[$iteration])->setAutoSize(true);
                $spreadsheet->getActiveSheet()->setCellValueExplicit(
                    self::CELLS[$iteration].'1',
                    $item,
                    DataType::TYPE_STRING
                );

                $iteration++;
            }

            // Number of cells
            $number_of_cells = count($head);

            // Print data
            $iteration = 2;

            foreach ($data as $item) {
                $item_indexed = array_values($item);

                for ($i = 1; $i <= $number_of_cells; $i++) {
                    $data_type = isset($data_types[$i - 1]['index'])
                        ? $data_types[$i - 1]['type']
                        : '';

                    match ($data_type) {
                        'TEXT' => $spreadsheet->getActiveSheet()->setCellValueExplicit(
                            self::CELLS[$i].$iteration,
                            $item_indexed[$i - 1],
                            DataType::TYPE_STRING
                        ),
                        default => $spreadsheet->setActiveSheetIndex(0)->setCellValue(
                            self::CELLS[$i].$iteration,
                            $item_indexed[$i - 1]
                        ),
                    };

                }

                $iteration++;
            }
        }

        return false;
    }

    /**
     * Allowed types for export
     *
     * @return list<string>
     */
    public static function allowed_types(): array
    {
        return self::ALLOWED_TYPES;
    }
}
