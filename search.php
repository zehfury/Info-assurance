<?php
/**
 * search.php — Vulnerable user-search endpoint for the SQL Injection Training Lab.
 *
 * Accepts `id` (or `search`) via GET or POST and concatenates it directly
 * into a SQL string with no sanitization. This is the deliberate teaching
 * vulnerability of this lab — see lesson.php for the payloads it enables.
 */

$standalone = !defined('SQLI_LAB_INCLUDED');

require_once 'db.php';

$input = $_REQUEST['id'] ?? $_REQUEST['search'] ?? '';

// INTENTIONALLY VULNERABLE — teaching example, never do this in production.
$query = "SELECT id, username, first_name, last_name, email, fake_password_hash FROM users WHERE id = " . $input;

if ($standalone) {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Search Results — SQLi Training Lab</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">';
    echo '<link rel="stylesheet" href="style.css"></head><body>';
    echo '<div class="banner">Intentionally vulnerable. Run on localhost only. Never deploy to a public network.</div>';
    echo '<main class="content"><div class="content-inner"><div class="card"><h2>Results</h2>';
}

echo '<div class="code-block">';
echo '<div class="code-block-header"><span class="code-block-label">Executed SQL Query</span>';
echo '<button type="button" class="copy-btn" data-copy-target="sql-query-text">Copy</button></div>';
echo '<pre class="code-block-body"><code id="sql-query-text">' . htmlspecialchars($query, ENT_QUOTES) . '</code></pre>';
echo '</div>';

try {
    $stmt = $pdo->query($query);
    $rows = $stmt->fetchAll();

    if (count($rows) === 0) {
        echo '<p class="no-results">No results found.</p>';
    } else {
        echo '<table class="results">';
        echo '<tr><th>ID</th><th>Username</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Password Hash</th></tr>';
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $value) {
                echo '<td>' . htmlspecialchars((string)$value, ENT_QUOTES) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    }
} catch (PDOException $e) {
    // Verbose error output is part of the lesson — shows how error messages leak schema info.
    echo '<div class="sql-error">SQL Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '</div>';
}

if ($standalone) {
    echo '</div></div></main><script src="script.js"></script></body></html>';
}
