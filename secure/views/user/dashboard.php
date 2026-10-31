<?php /** @var array $profile */ ?>
<div class="card">
    <span class="eyebrow">Account</span>
    <h2>Welcome, <?= e($profile['first_name']) ?></h2>
    <table class="results">
        <tr><th>Username</th><td><?= e($profile['username']) ?></td></tr>
        <tr><th>Name</th><td><?= e($profile['first_name'] . ' ' . $profile['last_name']) ?></td></tr>
        <tr><th>Email</th><td><?= e($profile['email']) ?></td></tr>
        <tr><th>Member since</th><td><?= e($profile['created_at']) ?></td></tr>
    </table>
</div>
