<?php
/**
 * index.php — Entry point for the SQL Injection Training Lab.
 * Authenticates users against the secure accounts table before opening the secure app.
 */
declare(strict_types=1);

require_once __DIR__ . '/nav.php';
require_once __DIR__ . '/db.php';

if (isset($_SESSION['user'])) {
    header('Location: secure/index.php');
    exit;
}

$errors = [];
$identifier = '';
$dummyHash = '$2y$10$6J5yXx5Om7jB0qVOQrps6OkEfVAkMXof9umcxl/Hv6lhrfAF0jsv6';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $identifier = trim(is_string($_POST['identifier'] ?? null) ? $_POST['identifier'] : '');
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $csrf = is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : '';

    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif ($identifier === '' || $password === '') {
        $errors[] = 'Enter your username or email and password.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, username, first_name, last_name, password_hash, role
               FROM users
              WHERE (username = :username OR email = :email)
                AND password_hash IS NOT NULL
              LIMIT 1'
        );
        $stmt->execute([
            ':username' => $identifier,
            ':email'    => $identifier,
        ]);
        $account = $stmt->fetch() ?: null;

        $valid = password_verify($password, (string) ($account['password_hash'] ?? $dummyHash))
            && $account !== null;

        if ($valid) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'       => (int) $account['id'],
                'username' => $account['username'],
                'name'     => trim($account['first_name'] . ' ' . $account['last_name']),
                'role'     => $account['role'],
            ];

            header('Location: secure/index.php');
            exit;
        }

        $errors[] = 'Invalid username or password.';
    }
}

$csrf = $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Secure Login — SQLi Training Lab</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="secure/assets/secure.css">
</head>
<body>
<div class="banner">Hardened area: prepared statements + attack logging. Local lab only.</div>

<header class="app-header">
    <div class="header-title">
        <h1>SQLi Training Lab</h1>
        <span class="header-subtitle">Secure Accounts</span>
    </div>
    <div class="badges">
        <span class="badge badge-slate">Guest</span>
        <span class="badge badge-blue">Secure Login</span>
    </div>
</header>

<div class="layout">
    <main class="content">
        <div class="content-inner">
            <div class="card auth-card">
                <span class="eyebrow">Account</span>
                <h2>Secure Login</h2>
                <p>Use your username or email and password.</p>

                <?php if ($errors): ?>
                    <div class="alert alert-error" role="alert">
                        <?php foreach ($errors as $error): ?><div><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div><?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form class="form" method="post" action="index.php" autocomplete="off">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

                    <div class="form-group">
                        <label for="identifier">Username or email</label>
                        <input type="text" id="identifier" name="identifier" value="<?= htmlspecialchars($identifier, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" maxlength="200" autofocus>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" maxlength="200">
                    </div>

                    <button type="submit" class="btn">Log in</button>
                </form>

                <p class="form-foot">No account yet? <a href="secure/index.php?r=register">Register</a></p>
            </div>
        </div>
    </main>
</div>
</body>
</html>
