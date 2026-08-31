# Web and sites

## HTTP services

```php
use PHP_Library\Core\Services\Web_Service;

$service = new Web_Service('https://api.example.com/items');
$result = $service->response([
    'user_agent' => 'Example application/1.0',
    'http_header' => ['Accept: application/json'],
    'return_transfer' => true,
]);

if ($result === false || ! $result['status']) {
    foreach ($service->get_error() as $error) {
        error_log($error);
    }
} else {
    $payload = json_decode($result['response'], true);
}
```

The response shape contains:

| Key | Meaning |
| --- | --- |
| `status` | `true` only for an HTTP 200 response |
| `code` | HTTP status code reported by cURL |
| `response` | Raw response body |

You may instantiate without a URL and call `set_url()` later. An unset URL
returns `false`. Passing a `data` array switches the request to JSON `POST` and
decodes the JSON response into an array. Other supported option keys are
`header`, `custom_request`, `post_fields`, `http_header`,
`return_transfer` and `no_body`.

## Site metadata and assets

```php
use PHP_Library\Core\Sites\Website;

$site = new Website([
    'name' => 'Example',
    'host' => 'https://example.com/',
    'made' => '2024',
    'language' => 'EN',
    'charset' => 'UTF-8',
    'description' => 'Example website',
]);

$site->add_to_images([
    'icon' => '/assets/icon.png',
    'logo' => '/assets/logo.png',
]);

$site->add_to_head([
    ['type' => 'link', 'path' => '/assets/app.css'],
]);

$site->add_to_bottom([
    ['type' => 'script', 'path' => '/assets/app.js'],
]);

echo $site->meta(['title' => 'Dashboard']);
echo $site->head();
echo $site->bottom();
echo $site->signature();
```

`name`, `host` and `made` are required. Missing values are recorded through
`get_error()` instead of throwing. The getters expose normalized configuration
and `get_server()` returns the current request's host, URI and page details.
`meta()` emits modern charset, viewport, description, author, icon and title
markup with escaped values. Meta keywords and Internet Explorer compatibility
markup are no longer emitted; the `keywords` constructor value and getter are
deprecated compatibility state.

Use `creator($key)` and `images($key)` to read configured entries.
`image_size($path)` returns `width`, `height` and `width_height` for a valid
image.
