<?php

/**
 * Website
 *
 * Use when working with website related data
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\Sites;

use Exception;
use PHP_Library\System\Examinations\Testing;

/**
 * Use when working with website related data
 */
class Website extends Testing
{
    /**
     * Server data holder
     */
    private array $server = [];

    /**
     * Website name
     */
    private ?string $name = null;

    /**
     * Website host
     */
    private ?string $host = null;

    /**
     * Year when website was made
     */
    private ?string $made = null;

    /**
     * Website language
     */
    private string $language = 'EN';

    /**
     * Website charset
     */
    private string $charset = 'UTF-8';

    /**
     * Website description
     */
    private string $description = 'Simple website';

    /**
     * Website keywords
     */
    private string $keywords = 'simple, website';

    /**
     * Head data
     */
    private array $head = [];

    /**
     * Bottom data
     */
    private array $bottom = [];

    /**
     * Available website images
     */
    private array $images = [
        'icon' => 'https://raw.githubusercontent.com/zlatanstajic/php-library/master/assets/img/phplibrary-icon.png',
        'logo' => 'https://raw.githubusercontent.com/zlatanstajic/php-library/master/assets/logos/logo-blue.png',
    ];

    /**
     * Website creator data
     */
    private array $creator = [
        'name' => 'Zlatan Stajić',
        'website' => 'https://www.zlatanstajic.com/',
        'email' => 'contact@zlatanstajic.com',
    ];

    /**
     * Head and bottom data calss
     */
    private const array CALLS = [
        'css' => [
            'ordinary' => 'link',
            'custom' => 'link-custom',
        ],
        'javascript' => [
            'ordinary' => 'script',
            'custom' => 'script-custom',
        ],
    ];

    /**
     * Class constructor method
     *
     *
     * @return void
     */
    public function __construct(array $params)
    {
        $host = $_SERVER['HTTP_HOST'] ?? null;

        $self = $_SERVER['PHP_SELF'] ?? null;

        $request_uri = $_SERVER['REQUEST_URI'] ?? null;

        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        $this->set_server([
            'location' => $host.$self,
            'referer' => $referer,
            'host' => $host,
            'uri' => $request_uri,
            'path' => dirname($self),
            'page' => basename($self),
        ]);

        isset($params['name'])
            ? $this->set_name($params['name'])
            : $this->set_error('Please set "name" parameter when using constructor');

        isset($params['host'])
            ? $this->set_host($params['host'])
            : $this->set_error('Please set "host" parameter when using constructor');

        isset($params['made'])
            ? $this->set_made($params['made'])
            : $this->set_error('Please set "made" parameter when using constructor');

        empty($params['language'])
            ? null
            : $this->set_language($params['language']);

        empty($params['charset'])
            ? null
            : $this->set_charset($params['charset']);

        empty($params['description'])
            ? null
            : $this->set_description($params['description']);

        empty($params['keywords'])
            ? null
            : $this->set_keywords($params['keywords']);
    }

    /**
     * Adding css and javascript tags to head of html
     */
    public function add_to_head(array $params): void
    {
        $this->head = $params;
    }

    /**
     * Adding css and javascript tags to bottom of html
     */
    public function add_to_bottom(array $params): void
    {
        $this->bottom = $params;
    }

    /**
     * Adding images to website
     */
    public function add_to_images(array $params, bool $to_merge = false): void
    {
        if ($to_merge) {
            $this->images = array_merge($this->images, $params);
        } else {
            $this->images = $params;
        }
    }

    /**
     * Adding data about website creator
     */
    public function add_to_creator(array $params, bool $to_merge = false): void
    {
        if ($to_merge) {
            $this->creator = array_merge($this->creator, $params);
        } else {
            $this->creator = $params;
        }
    }

    /**
     * Prints meta tags
     *
     * If no title was given, prints website name
     *
     *
     * @return string $meta
     */
    public function meta(array $params = []): string
    {
        $meta = '';

        $title = $params['title'] ?? '';
        $shortcut_icon = $params['shortcut_icon'] ?? ($this->images['icon'] ?? '');
        $touch_icon = $params['touch_icon'] ?? ($this->images['icon'] ?? '');

        if (isset($params['google_site_verification'])) {
            $meta .= '<meta name="google-site-verification" content="';
            $meta .= self::escape($params['google_site_verification']);
            $meta .= '">';
            $meta .= PHP_EOL;
        }

        $meta .= '<meta charset="'.self::escape($this->get_charset()).'">'.PHP_EOL;
        $meta .= '<meta name="viewport" content="width=device-width, initial-scale=1">'.PHP_EOL;
        $meta .= '<meta name="description" content="';
        $meta .= self::escape($this->get_description());
        $meta .= '">';
        $meta .= PHP_EOL;
        $meta .= '<meta name="author" content="';
        $meta .= self::escape((string) ($this->creator['name'] ?? ''));
        $meta .= '">';
        $meta .= PHP_EOL;
        $meta .= '<meta name="apple-mobile-web-app-capable" content="yes">';
        $meta .= PHP_EOL;

        // Touch icon image size
        $touch_icon_image_size = $touch_icon !== ''
            ? $this->image_size($touch_icon)
            : false;

        if (! is_bool($touch_icon_image_size)) {
            $meta .= '<link rel="apple-touch-icon" sizes="';
            $meta .= $touch_icon_image_size['width_height'];
            $meta .= '" href="';
            $meta .= self::escape($touch_icon);
            $meta .= '">';
            $meta .= PHP_EOL;
        }

        if ($shortcut_icon !== '') {
            $meta .= '<link rel="icon" href="';
            $meta .= self::escape($shortcut_icon);
            $meta .= '">'.PHP_EOL;
        }

        $meta .= '<title>';
        $meta .= self::escape((string) (empty($title) ? $this->get_name() : $title));
        $meta .= '</title>'.PHP_EOL;

        return $meta;
    }

    /**
     * Printing values in head of html
     *
     * @return string $return
     */
    public function head(): string
    {
        $return = '';

        $return .= '<!-- HEAD -->'.PHP_EOL;

        if (empty($this->head)) {
            $return .= '<!-- NOT LOADED -->'.PHP_EOL;
        } else {
            foreach ($this->head as $head) {
                switch ($head['type']) {
                    case self::CALLS['css']['ordinary']:

                        $return .= '<link rel="stylesheet" href="';
                        $return .= $head['path'];
                        $return .= '">'.PHP_EOL;
                        break;

                    case self::CALLS['javascript']['ordinary']:

                        $return .= '<script src="';
                        $return .= $head['path'];
                        $return .= '"></script>'.PHP_EOL;
                        break;

                    case self::CALLS['css']['custom']:

                        $return .= '<style>';
                        $return .= $head['path'];
                        $return .= '</style>'.PHP_EOL;
                        break;

                    case self::CALLS['javascript']['custom']:

                        $return .= '<script>';
                        $return .= $head['path'];
                        $return .= '</script>'.PHP_EOL;
                        break;

                }
            }
        }

        $return .= '<!-- /HEAD -->'.PHP_EOL;

        return $return;
    }

    /**
     * Printing values in bottom of html
     *
     * @return string $return
     */
    public function bottom(): string
    {
        $return = '';

        $return .= '<!-- BOTTOM -->'.PHP_EOL;

        if (empty($this->bottom)) {
            $return .= '<!-- NOT LOADED -->'.PHP_EOL;
        } else {
            foreach ($this->bottom as $bottom) {
                switch ($bottom['type']) {
                    case self::CALLS['css']['ordinary']:

                        $return .= '<link rel="stylesheet" href="';
                        $return .= $bottom['path'];
                        $return .= '">'.PHP_EOL;
                        break;

                    case self::CALLS['javascript']['ordinary']:

                        $return .= '<script src="';
                        $return .= $bottom['path'];
                        $return .= '"></script>'.PHP_EOL;
                        break;

                    case self::CALLS['css']['custom']:

                        $return .= '<style>';
                        $return .= $bottom['path'];
                        $return .= '</style>'.PHP_EOL;
                        break;

                    case self::CALLS['javascript']['custom']:

                        $return .= '<script>';
                        $return .= $bottom['path'];
                        $return .= '</script>'.PHP_EOL;
                        break;

                }
            }
        }

        $return .= '<!-- /BOTTOM -->'.PHP_EOL;

        return $return;
    }

    /**
     * Printing creator data
     */
    public function creator(string $creator): mixed
    {
        if (! empty($creator) && array_key_exists($creator, $this->creator)) {
            return $this->creator[$creator];
        }

        return false;
    }

    /**
     * Printing images
     */
    public function images(string $image): mixed
    {
        if (! empty($image) && array_key_exists($image, $this->images)) {
            return $this->images[$image];
        }

        return false;
    }

    /**
     * Printing image size value
     */
    public function image_size(string $image): mixed
    {
        try {
            $image_size = getimagesize($image);
        } catch (Exception $e) {
            $this->set_error($e->getMessage());
        }

        if (! empty($image_size)) {
            return [
                'width' => $image_size[0],
                'height' => $image_size[1],
                'width_height' => $image_size[0].'x'.$image_size[1],
                'type' => $image_size[2],
                'size' => $image_size[3],
                'bits' => $image_size['bits'] ?? null,
                'mime' => $image_size['mime'],
            ];
        }

        return false;
    }

    /**
     * Footer signature of creator and year when it was made
     *
     * When you want year span (eg. 2007-2017) set
     * first method parameter as TRUE.
     */
    public function signature(bool $always_made_year = false, bool $show_licence = false): string
    {
        $licence = '';

        $since = $current_year = date('Y');

        if ($always_made_year) {
            $since = $this->get_made();
        } elseif ($current_year != $this->get_made()) {
            $since = $this->get_made().'-'.$current_year;
        }

        if ($show_licence) {
            $licence = ' | All Rights Reserved';
        }

        return 'Copyright &#169; '.
            $since.
            ' | <a href="'.
            $this->creator['website'].
            '" target="_blank" rel="noopener">'.
            $this->creator['name'].
            '</a>'.
            $licence;
    }

    /**
     * Adds html comment to page view-source
     *
     * If language parameter is not passed to method,
     * default website language comment will be shown.
     *
     *
     * @return string $signature_hidden
     */
    public function signature_hidden(string $language = ''): string
    {
        $signature_hidden = '';

        if (empty($language)) {
            $language = $this->get_language();
        }

        $signature_hidden .= PHP_EOL;
        $signature_hidden .= '<!-- ';

        switch ($language) {
            case 'EN':
            case 'english':

                $signature_hidden .= 'Proudly built by: ';
                $signature_hidden .= $this->creator['name'];
                $signature_hidden .= '; Find me on ';
                $signature_hidden .= $this->creator['website'];

                break;

            default:

                $signature_hidden .= 'Ponosno izradio: ';
                $signature_hidden .= $this->creator['name'];
                $signature_hidden .= '; Pronadjite me na ';
                $signature_hidden .= $this->creator['website'];

        }

        $signature_hidden .= ' -->';
        $signature_hidden .= PHP_EOL;

        return $signature_hidden;
    }

    /**
     * Get server attribute
     *
     * @return array $this->server
     */
    public function get_server(): array
    {
        return $this->server;
    }

    /**
     * Set server attribute
     */
    private function set_server(array $params): void
    {
        $this->server = $params;
    }

    /**
     * Get name attribute
     *
     * @return string $this->name
     */
    public function get_name(): ?string
    {
        return $this->name;
    }

    /**
     * Set name attribute
     */
    private function set_name(string $value): void
    {
        $this->name = $value;
    }

    /**
     * Get host attribute
     *
     * @return string $this->host
     */
    public function get_host(): ?string
    {
        return $this->host;
    }

    /**
     * Set host attribute
     */
    private function set_host(string $value): void
    {
        $this->host = $value;
    }

    /**
     * Get made attribute
     *
     * @return string $this->made
     */
    public function get_made(): ?string
    {
        return $this->made;
    }

    /**
     * Set made attribute
     */
    private function set_made(string $value): void
    {
        $this->made = $value;
    }

    /**
     * Get language attribute
     *
     * @return string $this->language
     */
    public function get_language(): string
    {
        return $this->language;
    }

    /**
     * Set language attribute
     */
    private function set_language(string $value): void
    {
        $this->language = $value;
    }

    /**
     * Get charset attribute
     *
     * @return string $this->charset
     */
    public function get_charset(): string
    {
        return $this->charset;
    }

    /**
     * Set charset attribute
     */
    private function set_charset(string $value): void
    {
        $this->charset = $value;
    }

    /**
     * Get description attribute
     *
     * @return string $this->description
     */
    public function get_description(): string
    {
        return $this->description;
    }

    /**
     * Set description attribute
     */
    private function set_description(string $value): void
    {
        $this->description = $value;
    }

    /**
     * Get keywords attribute
     *
     * @return string $this->keywords
     *
     * @deprecated Search engines ignore meta keywords; the value is no longer rendered.
     */
    public function get_keywords(): string
    {
        return $this->keywords;
    }

    /**
     * Set keywords attribute
     *
     * @deprecated Search engines ignore meta keywords; retained for constructor compatibility.
     */
    private function set_keywords(string $value): void
    {
        $this->keywords = $value;
    }

    /**
     * Escape a value for HTML text or attribute output
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
