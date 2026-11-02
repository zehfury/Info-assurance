<?php
/**
 * nav.php — Shared sidebar for the vulnerable-lab pages (reps.php, lesson.php).
 *
 * Joins the secure app's session (same cookie name/params as secure/core/Session.php)
 * so the sidebar matches the one in secure/views/layout/main.php for guests, users and admins.
 * Must be required before any output is sent.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secureConfig = require __DIR__ . '/secure/config/config.php';
    session_name((string) $secureConfig['session_name']);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Render the sidebar. $active is 'lab' or 'lesson'. */
function lab_sidebar(string $active): string
{
    $h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $secure = static fn (string $route): string => 'secure/index.php?' . http_build_query(['r' => $route]);
    $link = static fn (string $href, string $label, bool $isActive = false): string =>
        '<a href="' . $h($href) . '"' . ($isActive ? ' class="active"' : '') . '>' . $h($label) . '</a>';

    $auth = $_SESSION['user'] ?? null;
    $out = '<nav class="sidebar">';

    if (!$auth) {
        $out .= $link($secure('login'), 'Secure Login')
              . $link($secure('vulnerable/login'), 'Vulnerable Login')
              . $link($secure('register'), 'Register')
              . $link($secure('admin/login'), 'Admin log in');
    } elseif (($auth['role'] ?? '') === 'admin') {
        $out .= $link($secure('admin'), 'Dashboard')
              . $link($secure('admin/logs'), 'SQLi logs')
              . $link($secure('admin/users'), 'Users');
    } else {
        $out .= $link($secure('dashboard'), 'My account');
    }

    $out .= '<div class="nav-sep"></div>'
          . $link('reps.php', 'Vulnerable lab', $active === 'lab')
          . $link('lesson.php', 'Lesson', $active === 'lesson');

    if ($auth) {
        $csrf = $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
        $out .= '<div class="nav-sep"></div>'
              . '<form method="post" action="' . $h($secure('logout')) . '">'
              . '<input type="hidden" name="csrf" value="' . $h($csrf) . '">'
              . '<button type="submit" class="nav-btn">Log out</button>'
              . '</form>';
    }

    return $out . '</nav>';
}
