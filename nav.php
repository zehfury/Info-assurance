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

// Same as the secure app: never cache, so Back after logout doesn't show a logged-in sidebar.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');

/**
 * Address of a page, relative to the project root (where reps.php and lesson.php live).
 * Clean addresses (/lab, /admin/login) when .htaccess rewriting is on, otherwise the file names.
 */
function lab_url(string $route): string
{
    if (!empty($_SERVER['CLEAN_URLS']) || !empty($_SERVER['REDIRECT_CLEAN_URLS'])) {
        return $route === '' ? './' : $route;
    }
    return match ($route) {
        ''       => 'index.php',
        'lab'    => 'reps.php',
        'lesson' => 'lesson.php',
        default  => 'index.php?' . http_build_query(['route' => $route]),
    };
}

$labUrl    = htmlspecialchars(lab_url('lab'), ENT_QUOTES);
$lessonUrl = htmlspecialchars(lab_url('lesson'), ENT_QUOTES);

// Opened as /reps.php or /lesson.php: show the clean address (/lab, /lesson) instead.
$labFile = basename((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH));
$labPages = ['reps.php' => 'lab', 'lesson.php' => 'lesson'];
if (isset($labPages[$labFile]) && lab_url('lab') === 'lab' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    $qs = (string) ($_SERVER['QUERY_STRING'] ?? '');
    header('Location: ' . $labPages[$labFile] . ($qs !== '' ? '?' . $qs : ''), true, 301);
    exit;
}

/** Render the sidebar. $active is 'lab' or 'lesson'. */
function lab_sidebar(string $active): string
{
    $h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $link = static fn (string $route, string $label, bool $isActive = false): string =>
        '<a href="' . $h(lab_url($route)) . '"' . ($isActive ? ' class="active"' : '') . '>' . $h($label) . '</a>';

    $auth = $_SESSION['user'] ?? null;
    $out = '<nav class="sidebar">';

    if (!$auth) {
        $out .= $link('', 'Secure Login')
              . $link('vulnerable/login', 'Vulnerable Login')
              . $link('register', 'Register')
              . $link('admin/login', 'Admin log in');
    } elseif (($auth['role'] ?? '') === 'admin') {
        $out .= $link('admin', 'Dashboard')
              . $link('admin/logs', 'SQLi logs')
              . $link('admin/action-logs', 'Action logs')
              . $link('admin/users', 'Users');
    } else {
        $out .= $link('dashboard', 'My account');
    }

    $out .= '<div class="nav-sep"></div>'
          . $link('lab', 'Vulnerable lab', $active === 'lab')
          . $link('lesson', 'Lesson', $active === 'lesson')
          . $link('xss', 'XSS lab');

    if ($auth) {
        $csrf = $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
        $out .= '<div class="nav-sep"></div>'
              . '<form method="post" action="' . $h(lab_url('logout')) . '">'
              . '<input type="hidden" name="csrf" value="' . $h($csrf) . '">'
              . '<button type="submit" class="nav-btn">Log out</button>'
              . '</form>';
    }

    return $out . '</nav>';
}
