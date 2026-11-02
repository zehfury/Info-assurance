<?php
/** @var string $content @var string $title @var ?array $auth @var ?array $flash @var string $csrf @var string $currentRoute */
$link = static function (string $route, string $label) use ($currentRoute): string {
    $active = $currentRoute === $route ? ' class="active"' : '';
    return '<a href="' . e(url($route)) . '"' . $active . '>' . e($label) . '</a>';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — SQLi Training Lab</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../style.css">
<link rel="stylesheet" href="assets/secure.css">
</head>
<body>
<div class="banner"><?= $currentRoute === 'vulnerable/login' ? 'Intentionally vulnerable login + attack logging. Local lab only.' : 'Hardened area: prepared statements + attack logging. Local lab only.' ?></div>

<header class="app-header">
    <div class="header-title">
        <h1>SQLi Training Lab</h1>
        <span class="header-subtitle"><?= $currentRoute === 'vulnerable/login' ? 'Vulnerable Login Lab' : 'Secure Accounts' ?></span>
    </div>
    <div class="badges">
        <?php if ($auth): ?>
            <span class="badge badge-slate"><?= e($auth['username']) ?></span>
            <span class="badge <?= $auth['role'] === 'admin' ? 'badge-red' : 'badge-blue' ?>"><?= e($auth['role']) ?></span>
        <?php else: ?>
            <span class="badge badge-slate">Guest</span>
        <?php endif; ?>
    </div>
</header>

<div class="layout">
    <nav class="sidebar">
        <?php if (!$auth): ?>
            <?= $link('login', 'Secure Login') ?>
            <?= $link('vulnerable/login', 'Vulnerable Login') ?>
            <?= $link('register', 'Register') ?>
            <?= $link('admin/login', 'Admin log in') ?>
        <?php elseif ($auth['role'] === 'admin'): ?>
            <?= $link('admin', 'Dashboard') ?>
            <?= $link('admin/logs', 'SQLi logs') ?>
            <?= $link('admin/users', 'Users') ?>
        <?php else: ?>
            <?= $link('dashboard', 'My account') ?>
        <?php endif; ?>

        <div class="nav-sep"></div>
        <a href="../reps.php">Vulnerable lab</a>
        <a href="../lesson.php">Lesson</a>

        <?php if ($auth): ?>
            <div class="nav-sep"></div>
            <form method="post" action="<?= e(url('logout')) ?>">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <button type="submit" class="nav-btn">Log out</button>
            </form>
        <?php endif; ?>
    </nav>

    <main class="content">
        <div class="content-inner">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </main>
</div>
</body>
</html>
