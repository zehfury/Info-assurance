<?php /** @var array $users */ ?>
<div class="card">
    <span class="eyebrow">Directory</span>
    <h2>Users (<?= e(count($users)) ?>)</h2>
    <div class="table-wrap">
        <table class="results">
            <thead><tr><th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Role</th><th>Can log in</th><th>Created</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['id']) ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['first_name'] . ' ' . $u['last_name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><span class="pill <?= $u['role'] === 'admin' ? 'pill-red' : '' ?>"><?= e($u['role']) ?></span></td>
                    <td><?= $u['can_login'] ? 'Yes' : 'No (seed data)' ?></td>
                    <td class="nowrap"><?= e($u['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
