<?php
declare(strict_types=1);

/** Dot-notation config lookup, e.g. config('db.host'). */
function config(string $key, mixed $default = null): mixed
{
    static $cfg = null;
    $cfg ??= require APP_PATH . '/config/config.php';

    $value = $cfg;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

/** HTML-escape for output. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * URL path of the project root, e.g. "/sqli-lab/". Worked out from the running script and ENTRY_DEPTH
 * (how many folders below the root the entry page is), so it works under any folder name.
 */
function root_url(): string
{
    static $root = null;
    if ($root === null) {
        $dir = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php')));
        for ($i = 0; $i < (defined('ENTRY_DEPTH') ? ENTRY_DEPTH : 1); $i++) {
            $dir = dirname($dir);
        }
        $root = rtrim(str_replace('\\', '/', $dir), '/') . '/';
    }
    return $root;
}

/** Absolute URL of a file relative to the project root: asset('style.css'). */
function asset(string $path): string
{
    return root_url() . ltrim($path, '/');
}

/**
 * Visitor's IP address in IPv4 form for the logs.
 * Browsers on the same machine usually connect over IPv6 as ::1, which is the same "this computer"
 * address as 127.0.0.1; IPv4-mapped addresses (::ffff:192.168.1.5) are unwrapped to 192.168.1.5.
 * A genuine IPv6-only client has no IPv4 equivalent and is stored as-is.
 */
function client_ip(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    if ($ip === '::1') {
        return '127.0.0.1';
    }
    if (preg_match('/^::ffff:(\d{1,3}(?:\.\d{1,3}){3})$/i', $ip, $m) === 1) {
        return $m[1];
    }
    return filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : 'unknown';
}

/** True when Apache's rewrite rules in /.htaccess are active, so clean addresses like /admin/login work. */
function clean_urls(): bool
{
    return !empty($_SERVER['CLEAN_URLS']) || !empty($_SERVER['REDIRECT_CLEAN_URLS']);
}

/**
 * Absolute URL for a route: url('admin/logs', ['page' => 2]) -> /sqli-lab/admin/logs?page=2
 * Without mod_rewrite it falls back to /sqli-lab/index.php?route=admin/logs&page=2.
 */
function url(string $route, array $params = []): string
{
    $query = static fn(array $p): string => $p === [] ? '' : '?' . http_build_query($p);
    $route = $route === 'login' ? '' : trim($route, '/');

    if (clean_urls()) {
        return asset($route) . $query($params);
    }
    $labFiles = ['lab' => 'reps.php', 'lesson' => 'lesson.php'];
    if (isset($labFiles[$route])) {
        return asset($labFiles[$route]) . $query($params);
    }
    return $route === '' ? asset('') . $query($params) : asset('index.php') . $query(['route' => $route] + $params);
}

/** Send a GET request that arrived on an old file address (e.g. secure/admin/login.php) to its clean address. */
function redirect_to_clean_url(string $route): void
{
    if (!clean_urls() || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        return;
    }
    $params = $_GET;
    unset($params['route'], $params['r']);
    header('Location: ' . url($route, $params), true, 301);
    exit;
}
