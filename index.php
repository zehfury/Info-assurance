<?php
/**
 * index.php — Entry point for the SQL Injection Training Lab.
 * Sends visitors to the secure app (login / register / admin dashboard).
 * The intentionally vulnerable search lab lives at reps.php.
 */
header('Location: secure/index.php');
exit;
