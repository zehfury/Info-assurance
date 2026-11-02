<div class="card auth-card">
    <span class="eyebrow">Student Portal</span>
    <h2>Vulnerable Login</h2>
    <div class="alert alert-error" role="note">Intentionally insecure — local classroom lab only. Never deploy.</div>
    <p>Try logging in normally, then compare the result with Secure Login. Detected SQL injection attempts are logged and use the configured admin email alerts.</p>

    <?php if ($errors): ?>
        <div class="alert alert-error" role="alert">
            <?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($account !== null): ?>
        <div class="alert alert-success" role="status">
            Lab login successful. Welcome, <?= e($account['full_name']) ?> (<?= e($account['username']) ?>).
            This demo session does not grant access to secure accounts or admin pages.
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(url('vulnerable/login')) ?>" autocomplete="off">
        <div class="form-group">
            <label for="username">Username</label>
            <input id="username" name="username" value="<?= e($username) ?>" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
        </div>
        <button type="submit" class="btn">Log in</button>
    </form>
    <p class="form-foot">Demo account: <code>gwen.falcon</code><br>Lab password: <code>a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4</code></p>
    <p class="form-foot"><a href="<?= e(url('login')) ?>">Open secure version</a></p>
</div>
