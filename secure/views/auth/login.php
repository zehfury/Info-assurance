<?php
/** @var string $heading @var string $subtitle @var string $formRoute @var bool $isAdmin @var string[] $errors @var string $identifier @var string $csrf */
?>
<div class="card auth-card">
    <span class="eyebrow"><?= $isAdmin ? 'Admin' : 'Account' ?></span>
    <h2><?= e($heading) ?></h2>
    <p><?= e($subtitle) ?></p>

    <?php if ($errors): ?>
        <div class="alert alert-error" role="alert">
            <?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(url($formRoute)) ?>" autocomplete="off">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

        <div class="form-group">
            <label for="identifier">Username or email</label>
            <input type="text" id="identifier" name="identifier" value="<?= e($identifier) ?>" maxlength="200" autofocus>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" maxlength="200">
        </div>

        <button type="submit" class="btn">Log in</button>
    </form>

    <?php if (!$isAdmin): ?>
        <p class="form-foot">No account yet? <a href="<?= e(url('register')) ?>">Register</a></p>
    <?php endif; ?>
</div>
