<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * The plugin's classes guard against direct file access with
 * `if (! defined('ABSPATH')) { exit; }`. Without this constant the very first
 * autoloaded class terminates the test runner with exit code 0 — a green,
 * empty run.
 */
if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

/**
 * Stub WordPress functions for unit testing.
 */
if (! function_exists('__')) {
    function __(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

if (! function_exists('esc_html__')) {
    function esc_html__(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

if (! function_exists('apply_filters')) {
    function apply_filters(string $hook_name, mixed $value, mixed ...$args): mixed
    {
        return $value;
    }
}
