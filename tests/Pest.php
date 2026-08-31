<?php

/**
 * Pest bootstrap
 */

/**
 * Create an empty, unique working directory for a test and return its path.
 *
 * Fixtures are always generated at run time - the library ships no binary
 * test data.
 */
function temp_dir(string $prefix = 'phplib'): string
{
    $path = sys_get_temp_dir().'/'.$prefix.'_'.bin2hex(random_bytes(6)).'/';

    mkdir($path, 0777, true);

    return $path;
}

/**
 * Recursively remove a directory created by temp_dir().
 */
function remove_dir(string $path): void
{
    if (! is_dir($path)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }

    rmdir($path);
}

/**
 * A 1x1 transparent PNG, written to $path. Keeps image tests off the network.
 */
function write_png(string $path): string
{
    file_put_contents($path, base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
    ));

    return $path;
}
