<?php
/**
 * lesson.php — Teaching panel for the SQL Injection Training Lab.
 * Rendered standalone or included below search results.
 */

$standalone = !defined('SQLI_LAB_INCLUDED');
require_once __DIR__ . '/nav.php';

if ($standalone) {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Lesson — SQLi Training Lab</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">';
    echo '<link rel="stylesheet" href="style.css"><link rel="stylesheet" href="secure/assets/secure.css"></head><body>';
    echo '<div class="banner">Intentionally vulnerable. Run on localhost only. Never deploy to a public network.</div>';
    echo '<header class="app-header"><div class="header-title"><h1>SQLi Training Lab</h1><span class="header-subtitle">Lesson</span></div>';
    echo '<div class="badges"><span class="badge badge-slate">Local Lab</span><span class="badge badge-red">SQL Injection</span><span class="badge badge-blue">Beginner</span></div></header>';
    echo '<div class="layout">' . lab_sidebar('lesson') . '<main class="content"><div class="content-inner">';
}
?>

<div class="card lesson">
    <span class="eyebrow">Step 3</span>
    <h2>Lesson: How This SQL Injection Works</h2>

    <h3>1. How this attack happens</h3>
    <div class="xss-flow">
        <div class="xss-flow-step"><span class="xss-flow-num">1</span><strong>You type an ID</strong><span>into the search box, for example <code>1</code></span></div>
        <div class="xss-flow-arrow" aria-hidden="true">&rarr;</div>
        <div class="xss-flow-step"><span class="xss-flow-num">2</span><strong>It is glued into the SQL</strong><span><code>"... WHERE id = " . $input</code>, with no checks</span></div>
        <div class="xss-flow-arrow" aria-hidden="true">&rarr;</div>
        <div class="xss-flow-step xss-flow-bad"><span class="xss-flow-num">3</span><strong>The database runs all of it</strong><span>extra SQL you typed becomes part of the command</span></div>
    </div>
    <p class="xss-fix-line"><strong>Watch the "Executed SQL Query" panel</strong> after each search: it shows exactly how your input changed the query.
       The ID is added <strong>without quotes</strong>, so payloads that don't need quotes work directly.</p>

    <h3>2. Payloads to try, in order</h3>
    <p>Press <strong>Try it</strong> to run a payload in the search box, then compare the results and the executed query.</p>
    <table class="sqli-payloads" data-try-url="<?= htmlspecialchars($labUrl ?? 'reps.php', ENT_QUOTES) ?>">
        <tr>
            <th>Payload</th>
            <th>What it does</th>
            <th>Expected result</th>
        </tr>
        <tr>
            <td><code>1</code></td>
            <td>Baseline — a normal, well-formed ID</td>
            <td>Returns one row (the user with id = 1)</td>
        </tr>
        <tr>
            <td><code>1 OR 1=1</code></td>
            <td>Adds a condition that is always true, so the WHERE clause matches every row</td>
            <td>Returns all 30 rows — boolean bypass</td>
        </tr>
        <tr>
            <td><code>1 UNION SELECT 1,2,3,4,5,6</code></td>
            <td>Appends a second SELECT to discover how many columns the original query returns</td>
            <td>Succeeds only when the column count matches (6 here) — confirms 6 columns</td>
        </tr>
        <tr>
            <td><code>1 UNION SELECT null,version(),database(),user(),null,null</code></td>
            <td>Uses the confirmed column count to pull MySQL/MariaDB version, current database, and current user</td>
            <td>Returns a row disclosing server version, DB name, and DB user</td>
        </tr>
        <tr>
            <td><code>1 UNION SELECT null,table_name,null,null,null,null FROM information_schema.tables</code></td>
            <td>Queries the database's own metadata tables instead of <code>users</code></td>
            <td>Returns a list of every table name in the database — table enumeration</td>
        </tr>
        <tr>
            <td><code>1 UNION SELECT null,column_name,null,null,null,null FROM information_schema.columns WHERE table_name='users'</code></td>
            <td>Once a table name is known (e.g. <code>users</code> from the row above), enumerate its column
            names the same way</td>
            <td>Returns every column name in <code>users</code> — the natural next step before targeting
            specific data</td>
        </tr>
    </table>

    <h3>3. Why it works</h3>
    <div class="xss-cards">
        <div class="xss-card xss-card-bad"><span class="xss-card-icon" aria-hidden="true">🧩</span><strong>Input glued into SQL</strong><span>The database can't tell your data from its own commands.</span><code class="xss-card-file">search.php</code></div>
        <div class="xss-card xss-card-bad"><span class="xss-card-icon" aria-hidden="true">🔢</span><strong>No input checks</strong><span>Nobody confirms the ID is really a number before using it.</span><code class="xss-card-file">search.php</code></div>
        <div class="xss-card xss-card-bad"><span class="xss-card-icon" aria-hidden="true">📢</span><strong>Error messages shown</strong><span>Raw database errors tell an attacker exactly what to fix next.</span><code class="xss-card-file">search.php</code></div>
    </div>

    <h3>4. How the secure app stops it</h3>
    <div class="xss-cards">
        <div class="xss-card xss-card-good"><span class="xss-card-icon" aria-hidden="true">🛡️</span><strong>Prepared statements</strong><span>Input is sent separately from the SQL, so it can only ever be a value.</span><code class="xss-card-file">secure/models/User.php</code></div>
        <div class="xss-card xss-card-good"><span class="xss-card-icon" aria-hidden="true">🔍</span><strong>Detection and alerts</strong><span>Attack patterns are logged and the admin gets an email.</span><code class="xss-card-file">SqliDetector.php</code></div>
        <div class="xss-card xss-card-good"><span class="xss-card-icon" aria-hidden="true">🤐</span><strong>Generic errors</strong><span>Visitors see a plain message; details go to the server log.</span><code class="xss-card-file">secure/bootstrap.php</code></div>
    </div>

    <details class="sqli-more">
        <summary>Advanced notes: payloads that don't apply here, and database differences</summary>
    <h4>Payloads that don't apply here (and why)</h4>
    <ul>
        <li><strong>Comment-out (<code>1 -- </code>, <code>1#</code>):</strong> valid syntax, but this query has
        nothing after the injection point to comment out, so the result looks identical to the baseline. It
        becomes useful against a query that appends more SQL after your input, e.g.
        <code>WHERE id = $input AND status = 'active'</code>.</li>
        <li><strong>Quote-closing bypass (<code>1' OR '1'='1</code>):</strong> this only works when the
        application wraps your value in quotes before concatenating it (e.g. <code>WHERE id = '$input'</code>).
        This lab concatenates <code>id</code> unquoted, so the stray <code>'</code> breaks the query instead —
        try it yourself in the search box to see the raw error message, which is itself an example of verbose
        error leakage.</li>
    </ul>

    <h4>Version / dialect notes</h4>
    <ul>
        <li>MySQL and MariaDB both support <code>#</code> and <code>-- </code> (note the required space after
        the two dashes) as single-line comment markers.</li>
        <li><code>version()</code>, <code>database()</code>, and <code>user()</code> are MySQL/MariaDB
        built-in functions; other databases use different names (e.g. PostgreSQL uses
        <code>current_database()</code>, SQL Server uses <code>@@version</code> and <code>DB_NAME()</code>).</li>
        <li><code>information_schema</code> enumeration works across MySQL, MariaDB, PostgreSQL, and SQL Server,
        but table names and exact columns differ slightly per engine.</li>
        <li><code>UNION SELECT</code> requires the second query to return the exact same number of columns as
        the first, with compatible data types in each position — this is why attackers probe column count
        first with payloads like the one above.</li>
        <li>Whether a quote-closing payload (like <code>' OR '1'='1</code>) works at all depends entirely on
        whether the application wrapped your input in quotes before concatenating it — always confirm the
        query's quoting (via the "Executed SQL Query" panel above) before assuming a payload should work.</li>
    </ul>
    </details>
</div>

<?php
if ($standalone) {
    echo '</div></main></div><script src="script.js"></script></body></html>';
}
