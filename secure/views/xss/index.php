<?php
/** @var string $message @var array $findings @var array $examples */
$frameUrl = static fn(string $mode): string => url('xss/frame', ['mode' => $mode, 'message' => $message]);
$isPlain  = ($findings[0]['label'] ?? '') === 'Plain text';
$runsCode = (bool) array_intersect(array_column($findings, 'label'), ['Script tag', 'Event handler', 'javascript: URL']);

// "What the browser received", with the important parts highlighted.
$wrapOpen  = e('<div class="comment">');
$wrapClose = e('</div>');
$rawShown  = preg_replace('/&lt;.*?&gt;/s', '<mark class="xss-mark-bad">$0</mark>', e($message));            // tags the browser will obey
$safeShown = preg_replace('/&amp;(?:lt|gt|quot|#039|amp);/', '<mark class="xss-mark-good">$0</mark>', e(e($message))); // characters e() replaced

// Which special characters e() had to change in this comment.
$charMap = array_filter(
    ['<' => '&lt;', '>' => '&gt;', '"' => '&quot;', "'" => '&#039;', '&' => '&amp;'],
    static fn(string $from): bool => str_contains($message, $from),
    ARRAY_FILTER_USE_KEY
);

/** Suggestion chips with a ✓ on the ones already tried, plus a progress bar. */
$tried = $tried ?? [];
$chips = static function () use ($examples, $tried, $message): string {
    $done  = count($tried);
    $total = count($examples);
    ob_start(); ?>
    <div class="xss-progress">
        <span class="xss-progress-label"><?= $done === $total ? 'All attacks tried!' : "Attacks tried: $done of $total" ?></span>
        <progress class="xss-progress-bar" max="<?= $total ?>" value="<?= $done ?>"><?= $done ?> of <?= $total ?></progress>
    </div>
    <div class="xss-suggestions">
        <span class="xss-suggestions-label">Try one:</span>
        <?php foreach ($examples as $i => $ex): ?>
            <?php $cls = 'xss-chip' . (in_array($i, $tried, true) ? ' xss-chip-done' : '') . ($message === $ex['payload'] ? ' xss-chip-current' : ''); ?>
            <a class="<?= $cls ?>" href="<?= e(url('xss', ['message' => $ex['payload']])) ?>" title="<?= e($ex['payload']) ?>"><?= in_array($i, $tried, true) ? '✓ ' : '' ?><?= e($ex['name']) ?></a>
        <?php endforeach; ?>
    </div>
    <?php return (string) ob_get_clean();
};
?>
<div class="card">
    <span class="eyebrow">Step 1</span>
    <h2>Cross-Site Scripting (XSS) Lab</h2>
    <p>Post a comment to a pretend guestbook. The page shows it twice: the <strong>vulnerable</strong> side prints it exactly as typed,
       the <strong>safe</strong> side escapes it first. Type your own HTML or pick a suggestion.</p>

    <form class="form" method="get" action="<?= e(url('xss')) ?>">
        <?php if (!clean_urls()): ?><input type="hidden" name="route" value="xss"><?php endif; ?>
        <div class="form-group">
            <label for="message">Comment</label>
            <textarea id="message" name="message" rows="3" maxlength="1000" placeholder="e.g. <b>Hello</b>"><?= e($message) ?></textarea>
        </div>
        <button type="submit" class="btn">Post comment</button>
    </form>

    <?= $chips() ?>
</div>

<?php if ($message !== ''): ?>
<div class="card">
    <span class="eyebrow">Step 2</span>
    <h2>Results: same comment, two different outcomes</h2>
    <p>Both sides received <strong>exactly the same text</strong>. The only difference is <strong>one function call</strong> in the code that prints it.</p>

    <div class="grid-2">
        <div>
            <h3 class="xss-side xss-side-bad">Vulnerable side</h3>
            <iframe class="xss-frame" src="<?= e($frameUrl('unsafe')) ?>" sandbox="allow-scripts allow-modals" referrerpolicy="no-referrer" title="Vulnerable output"></iframe>

            <?php if ($runsCode): ?>
                <p class="xss-result xss-result-bad">Your JavaScript ran.</p>
            <?php elseif (!$isPlain): ?>
                <p class="xss-result xss-result-bad">Your HTML became part of the page.</p>
            <?php else: ?>
                <p class="xss-result xss-result-neutral">Plain text, so it looks the same on both sides.</p>
            <?php endif; ?>

            <div class="code-block">
                <div class="code-block-header"><span class="code-block-label">1. The PHP that printed it</span></div>
                <pre class="code-block-body"><code>echo '&lt;div class="comment"&gt;' . <mark class="xss-mark-bad">$message</mark> . '&lt;/div&gt;';</code></pre>
            </div>
            <div class="code-block">
                <div class="code-block-header"><span class="code-block-label">2. What the browser received</span></div>
                <pre class="code-block-body"><code><?= $wrapOpen . $rawShown . $wrapClose ?></code></pre>
            </div>
            <p class="xss-why"><strong>Why it ran:</strong> your text was pasted into the page <em>unchanged</em>. The browser can't tell it apart
               from the site's own code, so it obeys the <mark class="xss-inline-bad">highlighted tags</mark>.</p>
        </div>

        <div>
            <h3 class="xss-side xss-side-good">Safe side</h3>
            <iframe class="xss-frame" src="<?= e($frameUrl('safe')) ?>" sandbox="allow-scripts allow-modals" referrerpolicy="no-referrer" title="Safe output"></iframe>

            <p class="xss-result xss-result-good">Shown as plain text. Nothing ran.</p>

            <div class="code-block">
                <div class="code-block-header"><span class="code-block-label">1. The PHP that printed it</span></div>
                <pre class="code-block-body"><code>echo '&lt;div class="comment"&gt;' . <mark class="xss-mark-good">e(</mark>$message<mark class="xss-mark-good">)</mark> . '&lt;/div&gt;';</code></pre>
            </div>
            <div class="code-block">
                <div class="code-block-header"><span class="code-block-label">2. What the browser received</span></div>
                <pre class="code-block-body"><code><?= $wrapOpen . $safeShown . $wrapClose ?></code></pre>
            </div>
            <p class="xss-why"><strong>What stopped it:</strong> <code>e()</code> replaced the special characters with
               <mark class="xss-inline-good">safe codes</mark> before printing. The browser displays those as symbols, so there are no real tags to run.</p>
        </div>
    </div>

    <?php if ($charMap !== []): ?>
        <h3>What <code>e()</code> changed in your comment</h3>
        <table class="xss-map">
            <tr><th>You typed</th><th>e() printed</th><th>The browser shows</th></tr>
            <?php foreach ($charMap as $from => $to): ?>
                <tr><td><code><?= e($from) ?></code></td><td><code><?= e($to) ?></code></td><td><?= e($from) ?> as a plain symbol, never as code</td></tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    <p class="xss-note">The real code is in <code>secure/controllers/XssController.php</code> line 81, and <code>e()</code> is in
       <code>secure/core/helpers.php</code> line 23. It is one line: <code>htmlspecialchars($value, ENT_QUOTES, 'UTF-8')</code>.</p>
</div>

<div class="card lesson">
    <span class="eyebrow">Step 3</span>
    <h2>Lesson: XSS in plain words</h2>

    <h3>1. How XSS happens</h3>
    <div class="xss-flow">
        <div class="xss-flow-step"><span class="xss-flow-num">1</span><strong>Attacker types</strong><span>a comment with HTML or a script inside</span></div>
        <div class="xss-flow-arrow" aria-hidden="true">&rarr;</div>
        <div class="xss-flow-step"><span class="xss-flow-num">2</span><strong>Server prints it</strong><span>into the page <em>without</em> escaping</span></div>
        <div class="xss-flow-arrow" aria-hidden="true">&rarr;</div>
        <div class="xss-flow-step xss-flow-bad"><span class="xss-flow-num">3</span><strong>Browser obeys it</strong><span>it can't tell the comment from the site's own code</span></div>
    </div>
    <p class="xss-fix-line"><strong>The fix:</strong> step 2 uses <code>e()</code>, so in step 3 the browser only sees harmless text.</p>

    <h3>2. What your comment contained</h3>
    <div class="xss-cards">
        <?php foreach ($findings as $f): ?>
            <div class="xss-card"><strong><?= e($f['label']) ?></strong><span><?= e($f['detail']) ?></span></div>
        <?php endforeach; ?>
    </div>

    <h3>3. The whole difference is one function</h3>
    <div class="xss-compare">
        <div class="xss-compare-bad"><span class="xss-compare-tag">Vulnerable</span><code>&lt;?= $message ?&gt;</code><span>Prints the text as-is. Tags work, scripts run.</span></div>
        <div class="xss-compare-good"><span class="xss-compare-tag">Safe</span><code>&lt;?= e($message) ?&gt;</code><span>Turns <code>&lt; &gt; " ' &amp;</code> into safe codes first.</span></div>
    </div>
    <p class="xss-fix-line"><strong>Rule to remember:</strong> escape every piece of user input <em>when you print it</em>.</p>

    <h3>4. What a real attacker could do</h3>
    <div class="xss-cards">
        <div class="xss-card xss-card-bad"><span class="xss-card-icon" aria-hidden="true">🍪</span><strong>Steal the session</strong><span>and log in as the victim.</span></div>
        <div class="xss-card xss-card-bad"><span class="xss-card-icon" aria-hidden="true">🎣</span><strong>Show a fake login box</strong><span>on the real site to capture passwords.</span></div>
        <div class="xss-card xss-card-bad"><span class="xss-card-icon" aria-hidden="true">🎭</span><strong>Act as the victim</strong><span>post, change settings, or deface the page for everyone.</span></div>
    </div>

    <h3>5. How this app is protected</h3>
    <div class="xss-cards">
        <div class="xss-card xss-card-good"><span class="xss-card-icon" aria-hidden="true">🛡️</span><strong>Escaping with e()</strong><span>The main fix. Every value the app prints goes through it.</span><code class="xss-card-file">helpers.php</code></div>
        <div class="xss-card xss-card-good"><span class="xss-card-icon" aria-hidden="true">🚫</span><strong>Content-Security-Policy</strong><span>Backup: the browser refuses scripts written inside our pages.</span><code class="xss-card-file">bootstrap.php</code></div>
        <div class="xss-card xss-card-good"><span class="xss-card-icon" aria-hidden="true">🔒</span><strong>HttpOnly cookie</strong><span>Scripts can't read the login cookie.</span><code class="xss-card-file">Session.php</code></div>
        <div class="xss-card xss-card-good"><span class="xss-card-icon" aria-hidden="true">📦</span><strong>Sandboxed demo box</strong><span>Lets this lab's popup run without touching your session.</span><code class="xss-card-file">XssController.php</code></div>
    </div>

    <h3>6. Try the others</h3>
    <?= $chips() ?>
</div>
<?php endif; ?>
