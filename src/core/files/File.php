<?php

/**
 * File
 *
 * File-related operations
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\Files;

use Exception;

/**
 * File-related operations
 */
class File
{
    /**
     * Data for image method
     *
     * Public, and therefore part of the API - left as a static property so that
     * File::$image keeps working for callers that customise it.
     *
     * @var array{location: string, default: string}
     */
    public static array $image = [
        'location' => 'users/',
        'default' => 'default.png',
    ];

    /**
     * Errors occurred during execution
     *
     * @var list<string>
     */
    public static array $errors = [];

    /**
     * Get full image link
     */
    public static function image(string $name): string
    {
        $link = self::$image['location'];
        $link .= $name;

        $image_size = self::probe_image($link);

        if (empty($name) || ! $image_size) {
            $link = self::$image['location'];
            $link .= self::$image['default'];
        }

        return $link;
    }

    /**
     * Writing data to file
     */
    public static function write_to_file(string $file_location, string $write_data, bool $last_in = true): int|false
    {
        if (! empty($file_location) || ! empty($write_data)) {
            $new_data = $write_data.PHP_EOL;

            if (file_exists($file_location)) {
                $file = fopen($file_location, 'r');

                if (! empty($file)) {
                    $file_location = strval($file_location);
                    $file_size = intval(filesize($file_location));

                    // fread() rejects a length of 0 as of PHP 8, where PHP 7
                    // simply returned an empty string for an empty file.
                    $old_data = $file_size > 0
                        ? fread($file, $file_size)
                        : '';

                    $data = $last_in
                        ? $old_data.$new_data
                        : $new_data.$old_data;

                    fclose($file);

                    return self::resource_operation($file_location, $data, 'w');
                }
            } else {
                return self::resource_operation($file_location, $new_data, 'w');
            }
        }

        return false;
    }

    /**
     * Reading last data item from file
     */
    public static function read_from_file(string $file_location): string|false
    {
        if (file_exists($file_location)) {
            $f = fopen($file_location, 'r');

            if ($f) {
                $cursor = -1;

                fseek($f, $cursor, SEEK_END);

                $char = fgetc($f);

                while ($char === "\n" || $char === "\r") {
                    fseek($f, $cursor--, SEEK_END);

                    $char = fgetc($f);
                }

                $line = '';

                while ($char !== false && $char !== "\n" && $char !== "\r") {
                    $line = $char.$line;

                    fseek($f, $cursor--, SEEK_END);

                    $char = fgetc($f);
                }

                return $line;
            }
        }

        return false;
    }

    /**
     * Read file contents into an array
     *
     * @deprecated Use read_csv(), whose name describes the parsed format.
     *
     * @return array{status: bool, items: list<list<string>>}
     */
    public static function read_file_contents(string $file_location, bool $to_unlink = false): array
    {
        return self::read_csv($file_location, $to_unlink);
    }

    /**
     * Read a delimited file with support for quoting and multiple-line fields
     *
     * @return array{status: bool, items: list<list<string|null>>}
     */
    public static function read_csv(
        string $file_location,
        bool $to_unlink = false,
        string $separator = ';',
        string $enclosure = '"',
        string $escape = ''
    ): array {
        $items = [];
        $resource = $file_location !== '' && is_file($file_location)
            ? fopen($file_location, 'r')
            : false;

        if ($resource === false) {
            return ['status' => false, 'items' => []];
        }

        while (($row = fgetcsv($resource, null, $separator, $enclosure, $escape)) !== false) {
            if ($row !== [null]) {
                $items[] = $row;
            }
        }

        fclose($resource);

        if ($to_unlink) {
            unlink($file_location);
        }

        return ['status' => true, 'items' => $items];
    }

    /**
     * Prepare framework-neutral download metadata without emitting or exiting
     *
     * @return array{path: string, filename: string, headers: list<string>}|false
     */
    public static function prepare_download(string $url): array|false
    {
        if (! is_file($url) || ! is_readable($url)) {
            return false;
        }

        $filename = pathinfo($url, PATHINFO_BASENAME);
        $encoded_filename = rawurlencode($filename);

        return [
            'path' => $url,
            'filename' => $filename,
            'headers' => [
                'Content-Type: application/octet-stream',
                "Content-Disposition: attachment; filename*=UTF-8''".$encoded_filename,
                'Content-Length: '.filesize($url),
            ],
        ];
    }

    /**
     * Force file download
     *
     * Terminates the request, so it cannot be exercised in-process.
     *
     * @deprecated Use prepare_download() and let the application emit a response.
     */
    public static function force_download(string $url): void
    {
        $download = self::prepare_download($url);

        if (! headers_sent() && $download !== false) {
            foreach ($download['headers'] as $header) {
                header($header);
            }

            readfile($download['path']);

            exit;
        }
    }

    /**
     * Read the dimensions of a candidate image
     *
     * A missing local path is the ordinary "fall back to the default" case, so
     * it is answered without calling getimagesize() - which would otherwise
     * raise a warning for something that is not an error. Anything carrying a
     * URL scheme is still handed to getimagesize(), so remote images work.
     */
    private static function probe_image(string $link): array|false
    {
        if (! is_file($link) && ! preg_match('#^[a-z][a-z0-9+.\-]*://#i', $link)) {
            return false;
        }

        try {
            return getimagesize($link);
        } catch (Exception $e) {
            self::$errors[] = $e->getMessage();

            return false;
        }
    }

    /**
     * Use fopen to ensure that resource is available for operations
     */
    private static function resource_operation(string $file_location, string $data, string $operation = 'w'): int
    {
        $value = 0;

        $resource = fopen($file_location, $operation);

        if (! empty($resource)) {
            switch ($operation) {
                case 'w':

                    $value = fwrite($resource, $data);

            }
        }

        return intval($value);
    }
}
