<?php
/** @var string $message @var array $findings @var array $examples */
$frameUrl  = static fn(string $mode): string => url('xss/frame', ['mode' => $mode, 'message' => $message]);
$isPlain   = ($findings[0]['label'] ?? '') === 'Plain text';
$runsCode  = (bool) array_intersect(array_column($findings, 'label'), ['Script tag', 'Event handler', 'javascript: URL']);
?>
<div class="card">
    <span class="eyebrow">Step 1</span>
    <h2>Cross-Site Scripting (XSS) Lab</h2>
    <p>Post a comment to a pretend guestbook. It is shown twice: once by code that prints it <strong>raw</strong>
       (vulnerable) and once by code that <strong>escapes</strong> it first (safe). Type your own HTML or pick a suggestion.</p>

    <form class="form" method="get" action="<?= e(url('xss')) ?>">
            <?php if (!clean_urls()): ?><input type="hidden" name="route" value="xss"><?php endif; ?>
        <div class="form-group">
            <label for="message">Comment</label>
            <textarea id="message" name="message" rows="3" maxlength="1000" placeholder="e.g. <b>Hello</b>"><?= e($message) ?></textarea>
        </div>
        <button type="submit" class="btn">Post comment</button>
    </form>

    <div class="xss-suggestions">
        <span class="xss-suggestions-label">Try one:</span>
        <?php foreach ($examples as $ex): ?>
            <a class="xss-chip" href="<?= e(url('xss', ['message' => $ex['payload']])) ?>" title="<?= e($ex['payload']) ?>"><?= e($ex['name']) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($message !== ''): ?>
<div class="card">
    <span class="eyebrow">Step 2</span>
    <h2>Results</h2>

    <?php if ($runsCode): ?>
        <div class="alert alert-error" role="status">Your comment contains JavaScript. On the vulnerable side it <strong>runs</strong> (watch for the popup); on the safe side it is just text.</div>
    <?php elseif (!$isPlain): ?>
        <div class="alert alert-error" role="status">Your comment contains HTML. The vulnerable side renders it as part of the page; the safe side shows it as text.</div>
    <?php endif; ?>

    <div class="grid-2">
        <div>
            <h3 class="xss-side xss-side-bad">Vulnerable: printed raw</h3>
            <iframe class="xss-frame" src="<?= e($frameUrl('unsafe')) ?>" sandbox="allow-scripts allow-modals" referrerpolicy="no-referrer" title="Vulnerable output"></iframe>
            <div class="code-block">
                <div class="code-block-header"><span class="code-block-label">HTML the server sent</span></div>
                <pre class="code-block-body"><code><?= e('<div class="comment">' . $message . '</div>') ?></code></pre>
            </div>
        </div>
        <div>
            <h3 class="xss-side xss-side-good">Safe: escaped with htmlspecialchars()</h3>
            <iframe class="xss-frame" src="<?= e($frameUrl('safe')) ?>" sandbox="allow-scripts allow-modals" referrerpolicy="no-referrer" title="Safe output"></iframe>
            <div class="code-block">
                <div class="code-block-header"><span class="code-block-label">HTML the server sent</span></div>
                <pre class="code-block-body"><code><?= e('<div class="comment">' . e($message) . '</div>') ?></code></pre>
            </div>
        </div>
    </div>
    <p class="xss-note">Compare the two code boxes: on the safe side every <code>&lt;</code> became <code>&amp;lt;</code>, so the browser
       displays the characters instead of treating them as tags.</p>
</div>

<div class="card lesson">
    <span class="eyebrow">Step 3</span>
    <h2>Lesson: How This XSS Works</h2>

    <h3>1. What your comment did</h3>
    <table>
        <tr><th>Found</th><th>Why it matters</th></tr>
        <?php foreach ($findings as $f): ?>
            <tr><td><strong><?= e($f['label']) ?></strong></td><td><?= e($f['detail']) ?></td></tr>
        <?php endforeach; ?>
    </table>

    <h3>2. Why one side is vulnerable</h3>
    <p>Both sides receive exactly the same text. The only difference is one function call when it is printed:</p>
    <table>
        <tr><th>Vulnerable</th><th>Safe</th></tr>
        <tr><td><code>&lt;?= $message ?&gt;</code></td><td><code>&lt;?= e($message) ?&gt;</code></td></tr>
        <tr><td>Text goes into the page as-is, so the browser parses tags and runs scripts.</td>
            <td><code>e()</code> calls <code>htmlspecialchars()</code>, turning <code>&lt; &gt; " ' &amp;</code> into harmless entities.</td></tr>
    </table>

    <h3>3. Payloads to try</h3>
    <table>
        <tr><th>Payload</th><th>What it does</th><th>Expected result</th></tr>
        <?php foreach ($examples as $ex): ?>
            <tr>
                <td><a href="<?= e(url('xss', ['message' => $ex['payload']])) ?>"><code><?= e($ex['payload']) ?></code></a></td>
                <td><strong><?= e($ex['name']) ?>:</strong> <?= e($ex['what']) ?></td>
                <td><?= e($ex['expect']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>4. Why XSS is dangerous on a real site</h3>
    <p>A popup is only a harmless proof. Injected script runs with the victim's identity on that site, so on a real,
       unprotected page an attacker could:</p>
    <ul>
        <li>Read session cookies that aren't marked <code>HttpOnly</code> and take over the account.</li>
        <li>Show a fake login form on the real site's page to capture passwords.</li>
        <li>Perform actions as the victim (post, change settings, transfer data).</li>
        <li>Deface the page for every visitor when the comment is stored (<em>stored XSS</em>).</li>
    </ul>

    <h3>5. How this application defends itself</h3>
    <table>
        <tr><th>Defence</th><th>Where</th><th>What it does</th></tr>
        <tr><td>Output escaping</td><td><code>secure/core/helpers.php</code> — <code>e()</code></td><td>The main fix: every value printed in the app goes through <code>htmlspecialchars()</code>.</td></tr>
        <tr><td>Content-Security-Policy</td><td><code>secure/bootstrap.php</code></td><td>Backup layer: the browser refuses to run inline scripts on every app page, even if escaping is missed.</td></tr>
        <tr><td>HttpOnly session cookie</td><td><code>secure/core/Session.php</code></td><td>JavaScript cannot read the session cookie, so it can't be stolen by a script.</td></tr>
        <tr><td>Sandboxed demo frame</td><td><code>XssController::frame()</code></td><td>Lets this lab's popup run safely, isolated from your real session.</td></tr>
    </table>
</div>
<?php endif; ?>
