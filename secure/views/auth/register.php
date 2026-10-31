<?php
/** @var string[] $errors @var array<string,string> $old @var string $csrf */
?>
<div class="card auth-card">
    <span class="eyebrow">Account</span>
    <h2>Create an account</h2>

    <?php if ($errors): ?>
        <div class="alert alert-error" role="alert">
            <?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(url('register')) ?>" autocomplete="off">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First name</label>
                <input type="text" id="first_name" name="first_name" value="<?= e($old['first_name']) ?>" maxlength="50">
            </div>
            <div class="form-group">
                <label for="last_name">Last name</label>
                <input type="text" id="last_name" name="last_name" value="<?= e($old['last_name']) ?>" maxlength="50">
            </div>
        </div>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= e($old['username']) ?>" maxlength="50">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text" id="email" name="email" value="<?= e($old['email']) ?>" maxlength="100">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" maxlength="200">
                <small>8–72 characters</small>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" maxlength="200">
            </div>
        </div>

        <button type="submit" class="btn">Create account</button>
    </form>

    <p class="form-foot">Already registered? <a href="<?= e(url('login')) ?>">Log in</a></p>
</div>
