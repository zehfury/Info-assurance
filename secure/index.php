<?php
/**
 * Old address for the logged-in pages (/secure/index.php?r=dashboard).
 * GET requests are forwarded to the clean address (/dashboard); the front controller is /index.php.
 */
declare(strict_types=1);

define('ENTRY_DEPTH', 1);
require __DIR__ . '/bootstrap.php';

$route = trim((string) ($_GET['r'] ?? 'login'), '/');

redirect_to_clean_url($route);
run_route($route); // only reached without mod_rewrite, or for a POST to the old address
