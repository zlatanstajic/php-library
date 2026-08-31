<?php

/**
 * Sorter
 *
 * Sorts files to multiple folders
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\Files;

use PHP_Library\System\Examinations\Testing;

/**
 * Sorts files to multiple folders
 */
class Sorter extends Testing
{
    /**
     * Sorter report
     */
    private array $report = [
        'folders' => [
            'number' => [
                'created' => 0,
                'not_created' => 0,
            ],
            'report' => [
                'created' => [],
                'not_created' => [],
            ],
        ],
        'files' => [
            'number' => [
                'copied' => 0,
                'not_copied' => 0,
                'moved' => 0,
                'not_moved' => 0,
            ],
            'report' => [
                'copied' => [],
                'not_copied' => [],
                'moved' => [],
                'not_moved' => [],
            ],
        ],
    ];

    /**
     * Class constructor
     *
     * @param  array  $deploy  Deploy values
     * @return void
     */
    public function __construct(private array $deploy)
    {
        isset($this->deploy['where_to_read_files'])
            ? null
            : $this->deploy['where_to_read_files'] = '';

        isset($this->deploy['where_to_create_directories'])
            ? null
            : $this->deploy['where_to_create_directories'] = '';

        isset($this->deploy['folder_sufix'])
            ? null
            : $this->deploy['folder_sufix'] = '';

        isset($this->deploy['operation'])
            ? null
            : $this->deploy['operation'] = 'c';

        isset($this->deploy['overwrite'])
            ? null
            : $this->deploy['overwrite'] = false;

        isset($this->deploy['settings'])
            ? null
            : $this->deploy['settings'] = [
                'max_execution_time' => 3600,
            ];

        ini_set(
            'max_execution_time',
            $this->deploy['settings']['max_execution_time']
        );
    }

    /**
     * Deploy sorting process
     */
    public function deploy(): bool
    {
        if (! $this->has_errors()) {
            $this->create_directories();

            if (! $this->has_errors()) {
                $this->transport_files(
                    $this->get_files(),
                    $this->deploy['operation'],
                    $this->deploy['overwrite']
                );
            }
        }

        return ! $this->has_errors() && $this->is_sorting_successful();
    }

    /**
     * Crawl for files
     */
    private function get_files(): array
    {
        $arr_files = [];

        $files_param = 'where_to_read_files';

        if (file_exists($this->deploy[$files_param])) {
            $files = (array) scandir($this->deploy[$files_param]);
            $number_of_files = 0;
            $counter = 1;

            foreach ($files as $file) {
                if ($counter > 2) {
                    $file = strval($file);

                    if (stripos($file, '.')) {
                        $extension = pathinfo($file, PATHINFO_EXTENSION);
                        $extension_lowered = strtolower($extension);

                        if (
                            empty($this->deploy['types']) ||
                            in_array($extension_lowered, $this->deploy['types'])
                        ) {
                            array_push($arr_files, [
                                'path' => $this->deploy[$files_param].$file,
                                'directory' => $this->deploy[$files_param],
                                'file' => $file,
                                'title' => basename($file, '.'.$extension),
                            ]);

                            $number_of_files += 1;
                        }
                    }
                }

                $counter++;
            }
        } else {
            $this->set_error('Improperly set '.$files_param.' parameter');
        }

        return $arr_files;
    }

    /**
     * Create directories
     */
    private function create_directories(): void
    {
        $directories_param = 'where_to_create_directories';

        if (file_exists($this->deploy[$directories_param])) {
            $number_of_directories = 'number_of_directories';

            if (empty($this->deploy[$number_of_directories])) {
                $this->set_error('Please set '.$number_of_directories.' parameter');
            } else {
                for ($i = 0; $i < $this->deploy['number_of_directories']; $i++) {
                    $folder = $this->folder_name($i);

                    if (! file_exists($folder)) {
                        if (mkdir($folder) && ! $this->is_being_tested()) {
                            $this->report['folders']['number']['created']++;
                            array_push(
                                $this->report['folders']['report']['created'],
                                $folder
                            );
                        } else {
                            $this->report['folders']['number']['not_created']++;
                            array_push(
                                $this->report['folders']['report']['not_created'],
                                $folder
                            );
                        }
                    }
                }

            }
        } else {
            $this->set_error('Improperly set '.$directories_param.' parameter');
        }
    }

    /**
     * Transport files to created directories
     */
    private function transport_files(array $files, string $operation, bool $overwrite): void
    {
        if (! empty($files)) {
            foreach ($files as $item) {
                $location_from = $this->deploy['where_to_read_files'];
                $location_from .= $item['file'];

                $location_to = $this->deploy['where_to_create_directories'];
                $location_to .= substr($item['file'], 0, 3);
                $location_to .= $this->deploy['folder_sufix'];
                $location_to .= '/';
                $location_to .= $item['file'];

                if ($overwrite) {
                    $this->execute_operation(
                        $operation,
                        $location_from,
                        $location_to,
                        $item['file']
                    );
                } else {
                    if (! file_exists($location_to)) {
                        $this->execute_operation(
                            $operation,
                            $location_from,
                            $location_to,
                            $item['file']
                        );
                    }
                }
            }
        }
    }

    /**
     * Execute operation
     */
    private function execute_operation(
        string $operation,
        string $location_from,
        string $location_to,
        string $item
    ): void {
        match ($operation) {
            'm' => $this->move_files($location_from, $location_to, $item),
            default => $this->copy_files($location_from, $location_to, $item),
        };
    }

    /**
     * Information about sorting process
     */
    public function report(): array
    {
        $report = 'Folders created/not created: ';
        $report .= $this->report['folders']['number']['created'];
        $report .= '/';
        $report .= $this->report['folders']['number']['not_created'];
        $report .= '<br/>';
        $report .= 'Files copied/not copied: ';
        $report .= $this->report['files']['number']['copied'];
        $report .= '/';
        $report .= $this->report['files']['number']['not_copied'];
        $report .= '<br/>';
        $report .= 'Files moved/not moved: ';
        $report .= $this->report['files']['number']['moved'];
        $report .= '/';
        $report .= $this->report['files']['number']['not_moved'];
        $report .= '<br/>';

        return [
            'bool' => [
                'no_errors' => ! $this->has_errors(),
                'successful_sorting' => $this->is_sorting_successful(),
                'something_to_sort' => ! $this->has_nothing_to_sort(),
            ],
            'string' => $report,
            'array' => [
                'usage' => getrusage(),
                'result' => $this->report,
            ],
        ];
    }

    /**
     * Folder name
     */
    private function folder_name(int $i): string
    {
        $folder_prefix = match (strlen(strval($i))) {
            1 => '00'.$i,
            2 => '0'.$i,
            3 => $i,
            default => '',
        };

        return $this->deploy['where_to_create_directories'].
            $folder_prefix.
            $this->deploy['folder_sufix'];
    }

    /**
     * Copy files from one location to another
     */
    private function copy_files(string $location_from, string $location_to, string $file): void
    {
        if (copy($location_from, $location_to) && ! $this->is_being_tested()) {
            $this->report['files']['number']['copied']++;
            array_push($this->report['files']['report']['copied'], $file);
        } else {
            $this->report['files']['number']['not_copied']++;
            array_push($this->report['files']['report']['not_copied'], $file);
        }
    }

    /**
     * Move files from one location to another
     */
    private function move_files(string $location_from, string $location_to, string $file): void
    {
        if (rename($location_from, $location_to) && ! $this->is_being_tested()) {
            $this->report['files']['number']['moved']++;
            array_push($this->report['files']['report']['moved'], $file);
        } else {
            $this->report['files']['number']['not_moved']++;
            array_push($this->report['files']['report']['not_moved'], $file);
        }
    }

    /**
     * Check if it has nothing to sort
     */
    private function has_nothing_to_sort(): bool
    {
        $state = $this->operation_states();

        return empty($this->report['files']['number'][$state['1st']]) &&
            empty($this->report['files']['number'][$state['2nd']]);
    }

    /**
     * Check if sorting operation is successful
     */
    private function is_sorting_successful(): bool
    {
        $state = $this->operation_states();

        if (! $this->has_nothing_to_sort()) {
            return empty($this->report['files']['number'][$state['2nd']]);
        }

        return false;
    }

    /**
     * Deploy operation states
     */
    private function operation_states(): array
    {
        switch ($this->deploy['operation']) {
            case 'm':

                $first_state = 'moved';
                $second_state = 'not-moved';

                break;

            case 'c':
            default:

                $first_state = 'copied';
                $second_state = 'not-copied';

        }

        return [
            '1st' => $first_state,
            '2nd' => $second_state,
        ];
    }
}
