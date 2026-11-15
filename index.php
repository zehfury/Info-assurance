<?php
/**
 * index.php — Front controller for the whole secure app.
 *
 * With the rewrite rules in .htaccess, every page has a clean address:
 *   /                  Secure Login (users)        /admin/login        Admin log in
 *   /register          Create a user account       /admin, /admin/logs Admin pages
 *   /dashboard         User account page           /xss                XSS lab
 *   /lab, /lesson      Vulnerable lab pages (reps.php, lesson.php)
 *
 * Every route runs through secure/bootstrap.php, so all pages get prepared statements, CSRF checks,
 * SQL-injection detection + email alerts, action logging and flash messages.
 */
declare(strict_types=1);

define('ENTRY_DEPTH', 0);
require __DIR__ . '/secure/bootstrap.php';

$route = trim((string) ($_GET['route'] ?? $_GET['r'] ?? ''), '/'); // ?r= is the old style
$route = $route === '' ? 'login' : $route;

// Visited as /index.php or /index.php?r=...: show the clean address instead.
$path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
if (str_ends_with($path, '/index.php')) {
    redirect_to_clean_url($route);
}

run_route($route);
