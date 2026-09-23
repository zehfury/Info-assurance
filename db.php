<?php
/**
 * db.php — PDO connection for the SQL Injection Training Lab.
 * Local use only. Adjust credentials below to match your local MySQL/MariaDB setup.
 */

$DB_HOST = '127.0.0.1';
$DB_NAME = 'sqli_lab';
$DB_USER = 'root';
$DB_PASS = ''; // set your local root password here if you have one

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
