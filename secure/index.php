<?php
/**
 * Front controller for the secure area of the SQLi Training Lab.
 * Usage: http://localhost/<lab-folder>/secure/index.php?r=login
 */
declare(strict_types=1);

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
header("Content-Security-Policy: default-src 'self'; style-src 'self' https://fonts.googleapis.com; "
     . "font-src https://fonts.gstatic.com; frame-ancestors 'none'; form-action 'self'");

Session::start();

$routes = [
    'vulnerable/login' => [VulnerableAuthController::class, 'login'],
    'login'          => [AuthController::class,  'login'],
    'register'       => [AuthController::class,  'register'],
    'logout'         => [AuthController::class,  'logout'],
    'admin/login'    => [AuthController::class,  'adminLogin'],
    'dashboard'      => [UserController::class,  'dashboard'],
    'admin'          => [AdminController::class, 'dashboard'],
    'admin/logs'     => [AdminController::class, 'logs'],
    'admin/logs/clear' => [AdminController::class, 'clearLogs'],
    'admin/users'    => [AdminController::class, 'users'],
];

$route = trim((string) ($_GET['r'] ?? 'login'), '/');

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
