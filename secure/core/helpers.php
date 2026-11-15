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

/** Build a front-controller URL: url('admin/logs', ['page' => 2]). */
function url(string $route, array $params = []): string
{
    return 'index.php?' . http_build_query(['r' => $route] + $params);
}

/** Collapse IPv6 loopback / IPv4-mapped forms down to plain IPv4 where possible. */
function clientIp(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if ($ip === '::1') {
        return '127.0.0.1';
    }
    if (str_starts_with($ip, '::ffff:')) {
        return substr($ip, 7); // ::ffff:192.168.1.5 -> 192.168.1.5
    }
    return $ip;
}
