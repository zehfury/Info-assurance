<?php
/**
 * Shared start-up for every entry page of the secure app:
 *   /index.php                 user login + register   (ENTRY_DEPTH 0)
 *   /secure/index.php          everything after login  (ENTRY_DEPTH 1)
 *   /secure/admin/login.php    admin login             (ENTRY_DEPTH 2)
 *
 * Each entry page defines ENTRY_DEPTH (how many folders below the project root it lives),
 * requires this file, then calls run_route('<route>').
 */
declare(strict_types=1);

defined('ENTRY_DEPTH') || define('ENTRY_DEPTH', 1);
define('APP_PATH', __DIR__);
define('VIEW_PATH', APP_PATH . '/views');

require APP_PATH . '/core/helpers.php';

spl_autoload_register(static function (string $class): void {
    foreach (['core', 'models', 'controllers'] as $dir) {
        $file = APP_PATH . "/$dir/$class.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
// Don't let the browser keep copies of pages: after logout, Back must ask the server again
// (which redirects to the login page) instead of showing the old logged-in page.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');
header("Content-Security-Policy: default-src 'self'; style-src 'self' https://fonts.googleapis.com; "
     . "font-src https://fonts.gstatic.com; frame-ancestors 'none'; form-action 'self'");

Session::start();

/** Dispatch one route to its controller. Unknown routes get a 404. */
function run_route(string $route): void
{
    $routes = [
        'vulnerable/login'        => [VulnerableAuthController::class, 'login'],
        'login'                   => [AuthController::class,  'login'],
        'register'                => [AuthController::class,  'register'],
        'logout'                  => [AuthController::class,  'logout'],
        'admin/login'             => [AuthController::class,  'adminLogin'],
        'dashboard'               => [UserController::class,  'dashboard'],
        'admin'                   => [AdminController::class, 'dashboard'],
        'admin/logs'              => [AdminController::class, 'logs'],
        'admin/logs/clear'        => [AdminController::class, 'clearLogs'],
        'admin/action-logs'       => [AdminController::class, 'actionLogs'],
        'admin/action-logs/clear' => [AdminController::class, 'clearActionLogs'],
        'admin/users'             => [AdminController::class, 'users'],
        'xss'                     => [XssController::class, 'index'],
        'xss/frame'               => [XssController::class, 'frame'],
    ];

    if (!isset($routes[$route])) {
        http_response_code(404);
        echo 'Page not found.';
        exit;
    }

    [$class, $method] = $routes[$route];

    try {
        (new $class($route))->$method();
    } catch (Throwable $e) {
        error_log((string) $e);
        http_response_code(500);
        echo 'Something went wrong. Check that MySQL is running and that schema.sql and secure/test.sql have been imported.';
    }
}
