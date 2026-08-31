<?php

/**
 * Web_Service
 *
 * Web service related data
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\Services;

use CurlHandle;
use PHP_Library\System\Examinations\Testing;

/**
 * Web service related data
 */
class Web_Service extends Testing
{
    /**
     * Holds information about cURL availability
     */
    private bool $curl_enabled = false;

    /**
     * Holds cURL session
     */
    private ?CurlHandle $ch = null;

    /**
     * Web service URL
     */
    private string $url = '';

    /**
     * Class constructor method
     */
    public function __construct(string $url = '')
    {
        if (function_exists('curl_init')) {
            $this->curl_enabled = true;
        }

        if (! empty($url)) {
            $this->url = $url;
        }
    }

    /**
     * Set URL attribute
     */
    public function set_url(string $url): void
    {
        if (empty($url)) {
            $this->set_error('Please set URL');
        } else {
            $this->url = $url;
        }
    }

    /**
     * Gets response from web service
     *
     * @return array{status: bool, code: mixed, response: mixed}|false
     */
    public function response(array $params = []): array|false
    {
        $this->is_function_available('curl_init');

        if ($this->is_ready_for_initialisation()) {
            $this->session_initialize();

            $request = $this->optional_request($params);

            $this->transfer_options($request['parameters']);

            $response = $this->session_perform();
            $code = $this->transfer_information();

            $this->session_close();

            if ($request['is_optional']) {
                $response = json_decode((string) $response, true);
            }

            return [
                'status' => $this->convert_code($code),
                'code' => $code,
                'response' => $response,
            ];
        }

        return false;
    }

    /**
     * Checks if everything is ready for cURL initialisation
     */
    private function is_ready_for_initialisation(): bool
    {
        return $this->curl_enabled && ! empty($this->url);
    }

    /**
     * Initialize a cURL session and set attribute
     */
    private function session_initialize(): void
    {
        $ch = curl_init($this->url);

        if (! empty($ch)) {
            $this->ch = $ch;
        }

        if (empty($this->ch) || $this->is_being_tested()) {
            $this->set_error('Unable to initialize cURL handler');
        }

        if ($this->is_being_tested()) {
            $this->pop_error();
        }
    }

    /**
     * Perform a cURL session
     */
    private function session_perform(): string|bool
    {
        return curl_exec($this->ch);
    }

    /**
     * Close a cURL session
     *
     * The handle is simply released: curl_close() is a no-op since PHP 8.0 and
     * deprecated as of PHP 8.5, and the session closes when the handle is
     * collected.
     */
    private function session_close(): void
    {
        $this->ch = null;
    }

    /**
     * Get information regarding a specific transfer
     */
    private function transfer_information(): mixed
    {
        return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    }

    /**
     * Set an option for a cURL transfer
     */
    private function transfer_options(array $params): void
    {
        if (isset($params['header'])) {
            curl_setopt($this->ch, CURLOPT_HEADER, $params['header']);
        }

        if (isset($params['user_agent'])) {
            curl_setopt($this->ch, CURLOPT_USERAGENT, $params['user_agent']);
        }

        if (isset($params['custom_request'])) {
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $params['custom_request']);
        }

        if (isset($params['post_fields'])) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params['post_fields']);
        }

        if (isset($params['http_header'])) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $params['http_header']);
        }

        $return_transfer = $params['return_transfer'] ?? true;

        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, $return_transfer);

        $no_body = $params['no_body'] ?? false;

        curl_setopt($this->ch, CURLOPT_NOBODY, $no_body);
    }

    /**
     * Convert response code to service availability status
     *
     * @param  int  $code
     */
    private function convert_code(mixed $code): bool
    {
        return match ($code) {
            200 => true,
            default => false,
        };
    }

    /**
     * Sending optional request
     */
    private function optional_request(array $params): array
    {
        $is_optional = false;
        $exit_array = [];

        if (isset($params['data'])) {
            $is_optional = true;

            $data_string = json_encode($params['data']);
            $string_length = strlen(strval($data_string));

            $exit_array = array_merge($params, [
                'custom_request' => 'POST',
                'post_fields' => $data_string,
                'http_header' => [
                    'Content-Type: application/json',
                    'Content-Length: '.$string_length,
                ],
            ]);
        } else {
            $exit_array = $params;
        }

        return [
            'is_optional' => $is_optional,
            'parameters' => $exit_array,
        ];
    }
}
