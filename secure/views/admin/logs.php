<?php
/** @var array $rows @var int $total @var int $page @var int $pages @var ?string $endpoint @var array $endpoints @var string $csrf */
$params = static fn(int $p): array => array_filter(['endpoint' => $endpoint, 'page' => $p > 1 ? $p : null]);
?>
<div class="card">
    <span class="eyebrow">Security</span>
    <h2>SQL injection logs</h2>

    <div class="toolbar">
        <form method="get" action="index.php" class="inline-form">
            <input type="hidden" name="r" value="admin/logs">
            <label for="endpoint">Entry point</label>
            <select id="endpoint" name="endpoint">
                <option value="">All</option>
                <?php foreach ($endpoints as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $endpoint === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-ghost">Filter</button>
        </form>
        <span class="count"><?= e($total) ?> entries</span>
    </div>

    <?php if (!$rows): ?>
        <p class="no-results">No log entries<?= $endpoint ? ' for this entry point' : '' ?>.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="results">
                <thead><tr><th>#</th><th>Time</th><th>Entry point</th><th>Field</th><th>Payload</th><th>Detected as</th><th>IP</th><th>User agent</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e($row['id']) ?></td>
                        <td class="nowrap"><?= e($row['created_at']) ?></td>
                        <td><?= e($endpoints[$row['endpoint']] ?? $row['endpoint']) ?></td>
                        <td class="mono"><?= e($row['field_name']) ?></td>
                        <td class="payload"><?= e($row['payload']) ?></td>
                        <td><?php foreach (explode(', ', $row['matched_patterns']) as $p): ?><span class="pill"><?= e($p) ?></span> <?php endforeach; ?></td>
                        <td class="mono"><?= e($row['ip_address']) ?></td>
                        <td class="ua"><?= e($row['user_agent'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pages > 1): ?>
            <nav class="pagination" aria-label="Log pages">
                <?php if ($page > 1): ?><a href="<?= e(url('admin/logs', $params($page - 1))) ?>">Previous</a><?php endif; ?>
                <span>Page <?= e($page) ?> of <?= e($pages) ?></span>
                <?php if ($page < $pages): ?><a href="<?= e(url('admin/logs', $params($page + 1))) ?>">Next</a><?php endif; ?>
            </nav>
        <?php endif; ?>

        <details class="danger-zone">
            <summary>Clear all logs</summary>
            <p>This permanently deletes every entry, including those hidden by the filter.</p>
            <form method="post" action="<?= e(url('admin/logs/clear')) ?>">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <button type="submit" class="btn btn-danger">Delete all log entries</button>
            </form>
        </details>
    <?php endif; ?>
</div>
