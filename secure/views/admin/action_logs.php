<?php
/** @var array $rows @var int $total @var int $page @var int $pages @var ?string $action @var array $actions @var string $csrf */
$params = static fn(int $p): array => array_filter(['action' => $action, 'page' => $p > 1 ? $p : null]);
?>
<div class="card">
    <span class="eyebrow">Audit</span>
    <h2>Action logs</h2>
    <div class="toolbar">
        <form method="get" action="<?= e(url('admin/action-logs')) ?>" class="inline-form">
            <?php if (!clean_urls()): ?><input type="hidden" name="route" value="admin/action-logs"><?php endif; ?>
            <label for="action">Action</label>
            <select id="action" name="action">
                <option value="">All</option>
                <?php foreach ($actions as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $action === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-ghost">Filter</button>
        </form>
        <span class="count"><?= e($total) ?> entries</span>
    </div>

    <?php if (!$rows): ?>
        <p class="no-results">No action entries.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="results">
                <thead><tr><th>#</th><th>Time</th><th>Action</th><th>User</th><th>Identifier</th><th>Details</th><th>IP</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e($row['id']) ?></td>
                        <td class="nowrap"><?= e($row['created_at']) ?></td>
                        <td><?= e($actions[$row['action']] ?? $row['action']) ?></td>
                        <td><?= e($row['user_id'] ?? '') ?></td>
                        <td class="mono"><?= e($row['identifier'] ?? '') ?></td>
                        <td><?= e($row['details'] ?? '') ?></td>
                        <td class="mono"><?= e($row['ip_address']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($pages > 1): ?>
            <nav class="pagination" aria-label="Action log pages">
                <?php if ($page > 1): ?><a href="<?= e(url('admin/action-logs', $params($page - 1))) ?>">Previous</a><?php endif; ?>
                <span>Page <?= e($page) ?> of <?= e($pages) ?></span>
                <?php if ($page < $pages): ?><a href="<?= e(url('admin/action-logs', $params($page + 1))) ?>">Next</a><?php endif; ?>
            </nav>
        <?php endif; ?>
        <details class="danger-zone">
            <summary>Clear action logs</summary>
            <form method="post" action="<?= e(url('admin/action-logs/clear')) ?>">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <button type="submit" class="btn btn-danger">Delete all action entries</button>
            </form>
        </details>
    <?php endif; ?>
</div>
