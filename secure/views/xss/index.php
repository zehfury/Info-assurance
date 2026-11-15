<?php
/** @var string $message */
?>
<div class="card">
    <span class="eyebrow">Security lab</span>
    <h2>Cross-site scripting (XSS)</h2>
    <p>Local training page: enter HTML or JavaScript and compare an unsafe output sink with the escaped safe output.</p>

    <form class="form" method="post" action="<?= e(url('xss')) ?>">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <div class="form-group">
            <label for="message">Test message</label>
            <textarea id="message" name="message" rows="4" maxlength="1000"><?= e($message) ?></textarea>
        </div>
        <button type="submit" class="btn">Render message</button>
    </form>
</div>

<?php if ($message !== ''): ?>
    <div class="grid-2">
        <div class="card">
            <span class="eyebrow">Vulnerable example</span>
            <h2>Unsafe output</h2>
            <p class="xss-output"><?= $message ?></p>
        </div>
        <div class="card">
            <span class="eyebrow">Safe example</span>
            <h2>Escaped output</h2>
            <p><?= e($message) ?></p>
        </div>
    </div>
<?php endif; ?>
