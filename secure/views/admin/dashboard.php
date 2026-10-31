<?php
/** @var array $userCounts @var int $totalLogs @var int $last24h @var array $byEndpoint @var array $topSources @var array $recent @var array $endpoints */
?>
<div class="stats">
    <div class="stat"><span class="stat-value"><?= e($totalLogs) ?></span><span class="stat-label">SQLi attempts logged</span></div>
    <div class="stat"><span class="stat-value"><?= e($last24h) ?></span><span class="stat-label">Last 24 hours</span></div>
    <div class="stat"><span class="stat-value"><?= e($userCounts['total']) ?></span><span class="stat-label">Users in directory</span></div>
    <div class="stat"><span class="stat-value"><?= e($userCounts['registered']) ?></span><span class="stat-label">Accounts that can log in</span></div>
</div>

<div class="grid-2">
    <div class="card">
        <h2>Attempts by entry point</h2>
        <table class="results">
            <?php foreach ($endpoints as $key => $label): ?>
                <tr>
                    <td><a href="<?= e(url('admin/logs', ['endpoint' => $key])) ?>"><?= e($label) ?></a></td>
                    <td class="num"><?= e($byEndpoint[$key] ?? 0) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div class="card">
        <h2>Top source IPs</h2>
        <?php if (!$topSources): ?>
            <p class="no-results">No attempts recorded yet.</p>
        <?php else: ?>
            <table class="results">
                <?php foreach ($topSources as $src): ?>
                    <tr><td class="mono"><?= e($src['ip_address']) ?></td><td class="num"><?= e($src['attempts']) ?></td></tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h2>Latest attempts</h2>
    <?php if (!$recent): ?>
        <p class="no-results">No SQL injection attempts have been logged yet. Try one on the login form to see it appear here.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="results">
                <thead><tr><th>Time</th><th>Entry point</th><th>Field</th><th>Payload</th><th>Detected as</th><th>IP</th></tr></thead>
                <tbody>
                <?php foreach ($recent as $row): ?>
                    <tr>
                        <td class="nowrap"><?= e($row['created_at']) ?></td>
                        <td><?= e($endpoints[$row['endpoint']] ?? $row['endpoint']) ?></td>
                        <td class="mono"><?= e($row['field_name']) ?></td>
                        <td class="payload"><?= e($row['payload']) ?></td>
                        <td><?php foreach (explode(', ', $row['matched_patterns']) as $p): ?><span class="pill"><?= e($p) ?></span> <?php endforeach; ?></td>
                        <td class="mono"><?= e($row['ip_address']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p><a href="<?= e(url('admin/logs')) ?>">View all logs</a></p>
    <?php endif; ?>
</div>
