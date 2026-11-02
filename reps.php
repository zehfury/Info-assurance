<?php
/**
 * reps.php — Vulnerable user-search dashboard for the SQL Injection Training Lab.
 */
define('SQLI_LAB_INCLUDED', true);
require_once __DIR__ . '/nav.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SQLi Training Lab</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="secure/assets/secure.css">
</head>
<body>

<div class="banner">
    Intentionally vulnerable. Run on localhost only. Never deploy to a public network.
</div>

<header class="app-header">
    <div class="header-title">
        <h1>SQLi Training Lab</h1>
        <span class="header-subtitle">User Directory Dashboard</span>
    </div>
    <div class="badges">
        <span class="badge badge-slate">Local Lab</span>
        <span class="badge badge-red">SQL Injection</span>
        <span class="badge badge-blue">Beginner</span>
    </div>
</header>

<div class="layout">
    <?= lab_sidebar('lab') ?>

    <main class="content">
        <div class="content-inner">
            <div class="card">
                <span class="eyebrow">Step 1</span>
                <h2>User Directory Search</h2>
                <p>Search the fictional user directory by ID. Try entering a plain number, or explore the
                <a href="lesson.php">lesson panel</a> below for payloads that reveal how this search box works
                under the hood.</p>

                <form class="search-form" action="reps.php" method="GET">
                    <input type="text" name="id" placeholder="Enter user ID, e.g. 1" value="<?= htmlspecialchars($_GET['id'] ?? '', ENT_QUOTES) ?>" autofocus>
                    <button type="submit">Search</button>
                </form>
            </div>

            <?php if (isset($_GET['id'])): ?>
                <div class="card">
                    <span class="eyebrow">Step 2</span>
                    <h2>Results</h2>
                    <?php include 'search.php'; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['id'])): ?>
                <?php include 'lesson.php'; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="script.js"></script>
</body>
</html>
