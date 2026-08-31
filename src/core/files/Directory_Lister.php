<?php

/**
 * Directory_Lister
 *
 * Directory content retrieval
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\Files;

/**
 * Directory content retrieval
 */
class Directory_Lister
{
    /**
     * Number of files counter
     */
    private static int $number_of_files = 0;

    /**
     * Number of folders counter
     */
    private static int $number_of_folders = 0;

    /**
     * Report variable
     */
    private static array $crawled = [];

    /**
     * Directory location
     */
    private static string $directory = '';

    /**
     * Date format
     */
    private const string DATE_FORMAT = 'Y-m-d';

    /**
     * Time format
     */
    private const string TIME_FORMAT = 'H:m:i';

    /**
     * Method calls
     */
    private const array METHOD_CALLS = [
        'files' => 'files',
        'folders' => 'folders',
        'crawl' => 'crawl',
    ];

    /**
     * Forbidden characters in files
     */
    private const array FORBIDDEN_CHARACTERS = [
        '-',
        '+',
        '!',
        '#',
        '$',
        '%',
        '&',
        '(',
        ')',
        '‚',
        '~',
        ':',
        ';',
    ];

    /**
     * Prepare date for date check
     *
     *
     * @return array $searched
     */
    private static function prepare_date(array $params): array
    {
        $date = $params['date'];
        $date_start = $params['date_start'];
        $date_end = $params['date_end'];
        $years = $params['years'];
        $item = $params['item'];
        $searched = $params['searched'];

        if (empty($date_start)) {
            if (empty($years)) {
                $searched = array_merge($searched, $item);
            } else {
                $date = substr($date, 0, 4);

                foreach ($years as $given_year) {
                    if ($date == $given_year) {
                        $searched = array_merge($searched, $item);
                    }
                }
            }
        } else {
            if (empty($date_end)) {
                if ($date == $date_start) {
                    $searched = array_merge($searched, $item);
                }
            }

            if ($date >= $date_start && $date <= $date_end) {
                $searched = array_merge($searched, $item);
            }
        }

        return $searched;
    }

    /**
     * Checks dates for listed directory limits
     *
     *
     * @return array $searched
     */
    private static function check_date(array $params): array
    {
        $item = $params['item'];
        $date = $params['date'];
        $date_start = $params['date_start'];
        $date_end = $params['date_end'];
        $years = $params['years'];

        $searched = [];

        if (empty($years)) {
            $searched = self::prepare_date([
                'item' => $item,
                'date' => substr($date, 5),
                'date_start' => $date_start,
                'date_end' => $date_end,
                'years' => $years,
                'searched' => $searched,
            ]);
        } else {
            foreach ($years as $given_year) {
                $start = empty($date_start)
                    ? ''
                    : $given_year.'-'.$date_start;

                $end = empty($date_end)
                    ? ''
                    : $given_year.'-'.$date_end;

                $searched = self::prepare_date([
                    'item' => $item,
                    'date' => $date,
                    'date_start' => $start,
                    'date_end' => $end,
                    'years' => $years,
                    'searched' => $searched,
                ]);
            }
        }

        return $searched;
    }

    /**
     * Files and folders in depth
     */
    private static function depth(array $list, array $types = []): mixed
    {
        if (! empty($list)) {
            $list_of_paths = $list_of_folders = $list_of_files = [];

            foreach ($list as $folder) {
                $location = $folder.'/';

                $depth_folders = self::folders($location);
                $depth_files = self::files($location, $types);

                $list_of_paths = array_merge($list_of_paths, $depth_folders['path']);
                $list_of_folders = array_merge($list_of_folders, $depth_folders['folder']);
                $list_of_files = array_merge($list_of_files, $depth_files);
            }

            return [
                'paths' => $list_of_paths,
                'folders' => $list_of_folders,
                'files' => $list_of_files,
            ];
        }

        return false;
    }

    /**
     * Reading folder contents for given directory
     *
     *
     * @return array $data
     */
    private static function folders(string $directory = ''): array
    {
        empty($directory) ? $directory = self::$directory : self::$directory = $directory;

        $files = is_dir($directory) ? (array) scandir($directory) : [];
        $arr_folder = $arr_path = [];
        $counter = 1;

        foreach ($files as $folder) {
            $folder_first_character = substr(strval($folder), 0, 1);

            if (! in_array($folder_first_character, self::FORBIDDEN_CHARACTERS)) {
                if ($counter > 2) {
                    $path = $directory.$folder;

                    if (is_dir($path)) {
                        array_push($arr_path, $path);
                        array_push($arr_folder, $folder);

                        self::$number_of_folders += 1;
                    }
                }

                $counter++;
            }
        }

        return [
            'path' => $arr_path,
            'folder' => $arr_folder,
        ];
    }

    /**
     * Reading file contents for given directory
     *
     *
     * @return array $arr_files
     */
    private static function files(string $directory = '', array $types = []): array
    {
        $arr_files = [];

        empty($directory) ? $directory = self::$directory : self::$directory = $directory;

        if (file_exists($directory)) {
            $files = (array) scandir($directory);
            $counter = 1;

            foreach ($files as $file) {
                if ($counter > 2) {
                    $file = strval($file);

                    if (stripos($file, '.')) {
                        $extension = pathinfo($file, PATHINFO_EXTENSION);
                        $extension_lowered = strtolower($extension);

                        if (empty($types) || in_array($extension_lowered, $types)) {
                            $title = basename($file, '.'.$extension);

                            $path = $directory;
                            $path .= $file;

                            $path = str_replace('\\', '/', $path);
                            $directory = str_replace('\\', '/', $directory);

                            $data = [
                                'title' => $title,
                                'path' => $path,
                                'directory' => $directory,
                                'file' => $file,
                                'extension' => $extension,
                                'size' => filesize($path),
                                'date' => date(self::DATE_FORMAT, intval(filemtime($path))),
                                'time' => date(self::TIME_FORMAT, intval(filemtime($path))),
                            ];

                            array_push($arr_files, $data);

                            self::$number_of_files++;
                        }
                    }
                }

                $counter++;
            }
        }

        return $arr_files;
    }

    /**
     * Listing all files inside given directory
     */
    private static function crawl(array $params): mixed
    {
        $directory = $params['directory'] ?? '';
        $types = $params['types'] ?? [];
        $data = $params['data'] ?? [];

        if (empty($data)) {
            $list_of_paths = [];
            $list_of_folders = self::folders($directory);
            $list_of_files = self::files($directory, $types);

            $paths = $list_of_folders['path'];

            $depth = self::depth($paths, $types);

            if ($depth) {
                $paths = array_merge($list_of_paths, $depth['paths']);
                $files = array_merge($list_of_files, $depth['files']);

                self::$crawled = $files;

                if (! empty($paths)) {
                    self::crawl([
                        'types' => $types,
                        'data' => [
                            'paths' => $paths,
                            'files' => $files,
                        ],
                    ]);
                }
            }
        } else {
            $paths = $data['paths'];
            $files = $data['files'];

            self::$crawled = $files;

            if (! empty($paths)) {
                $depth = self::depth($paths, $types);

                if ($depth) {
                    $paths = $depth['paths'];
                    $files = array_merge($files, $depth['files']);

                    self::$crawled = $files;

                    if (empty($paths)) {
                        return true;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Listing specific directory reading results
     */
    public static function listing(array $params): mixed
    {
        $directory = $params['directory'];
        $method = $params['method'];

        $types = $params['types'] ?? [];

        $searched = [];

        $list = self::method_to_list($method, $directory, $types);

        if ($method !== self::METHOD_CALLS['folders']) {
            if (empty($list)) {
                return false;
            } else {
                $searched = self::filtering_by_date($list, $params);
            }
        } else {
            $searched = $list;
        }

        if (array_key_exists('folder', $searched)) {
            $count = count($searched['folder']);
            $max = self::$number_of_folders;
        } else {
            $count = count($searched);
            $max = self::$number_of_files;
        }

        array_multisort($searched);

        $data = [
            'listing' => $searched,
            'count' => $count,
            'max' => $max,
        ];

        self::$number_of_folders = self::$number_of_files = 0;

        return $data;
    }

    /**
     * Convert method call to list of items
     */
    private static function method_to_list(string $method, string $directory, array $types): array
    {
        switch ($method) {
            case self::METHOD_CALLS['folders']:

                return self::folders($directory);

            case self::METHOD_CALLS['files']:

                return self::files($directory, $types);

            case self::METHOD_CALLS['crawl']:

                self::crawl([
                    'directory' => $directory,
                    'types' => $types,
                ]);

                return self::$crawled;

            default: return [];
        }
    }

    /**
     * Files filtering by date
     *
     *
     * @return array $searched
     */
    private static function filtering_by_date(array $list, array $params): array
    {
        $delimiter = $params['delimiter'] ?? false;

        $reverse = $params['reverse'] ?? false;

        $date_start = $params['date_start'] ?? false;

        $date_end = $params['date_end'] ?? false;

        $years = $params['years'] ?? false;

        $searched = $checked = [];

        foreach ($list as $item) {
            $date = $item['date'] ?? null;

            $params = [
                'item' => $item,
                'date' => $date,
                'date_start' => $date_start,
                'date_end' => $date_end,
                'years' => $years,
            ];

            if (empty($delimiter)) {
                $checked = self::check_date($params);
            } else {
                if ($reverse) {
                    if (stripos($item['title'], (string) $delimiter) === false) {
                        $checked = self::check_date($params);
                    } else {
                        $checked = [];
                    }
                } else {
                    if (stripos($item['title'], (string) $delimiter) !== false) {
                        $checked = self::check_date($params);
                    } else {
                        $checked = [];
                    }
                }
            }

            if (! empty($checked)) {
                array_push($searched, $checked);
            }
        }

        return $searched;
    }
}
