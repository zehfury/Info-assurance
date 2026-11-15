<?php
/**
 * Old address for the admin login. GET requests are forwarded to /admin/login.
 * Only accounts with role 'admin' can sign in. User accounts are rejected with the generic message.
 */
declare(strict_types=1);

define('ENTRY_DEPTH', 2);
require __DIR__ . '/../bootstrap.php';

redirect_to_clean_url('admin/login');
run_route('admin/login'); // only reached without mod_rewrite, or for a POST to the old address
