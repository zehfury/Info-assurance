<?php
/**
 * lesson.php — Teaching panel for the SQL Injection Training Lab.
 * Rendered standalone or included below search results.
 */

$standalone = !defined('SQLI_LAB_INCLUDED');

if ($standalone) {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Lesson — SQLi Training Lab</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">';
    echo '<link rel="stylesheet" href="style.css"></head><body>';
    echo '<div class="banner">Intentionally vulnerable. Run on localhost only. Never deploy to a public network.</div>';
    echo '<header class="app-header"><div class="header-title"><h1>SQLi Training Lab</h1><span class="header-subtitle">Lesson</span></div>';
    echo '<div class="badges"><span class="badge badge-slate">Local Lab</span><span class="badge badge-red">SQL Injection</span><span class="badge badge-blue">Beginner</span></div></header>';
    echo '<div class="layout"><nav class="sidebar"><a href="index.php">User Search</a><a href="lesson.php" class="active">Lesson: SQL Injection</a></nav><main class="content"><div class="content-inner">';
}
?>

<div class="card lesson">
    <span class="eyebrow">Step 3</span>
    <h2>Lesson: How This SQL Injection Works</h2>

    <h3>1. What this page does</h3>
    <p>
        The search box on the dashboard takes whatever you type into the <code>id</code> parameter and glues it
        directly onto the end of a SQL string with plain string concatenation
        (<code>"... WHERE id = " . $input</code>). No escaping, no parameter binding, no type checking happens
        before that string is sent to the database — so anything you type becomes part of the SQL the database
        executes, not just a value being searched for. Because the value is concatenated <strong>unquoted</strong>
        (a numeric context), payloads that don't need quotes work directly; payloads written for a quoted
        context behave differently here, as shown below.
    </p>

    <h3>2. Payloads to try</h3>
    <table>
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

    <p>
        Two payloads that show up in most SQLi cheat sheets are <strong>not</strong> included above because they
        don't do anything useful against this specific endpoint: a trailing comment (<code>1 -- </code> or
        <code>1#</code>) has nothing to strip off since the query ends right after your input, and a
        quote-closing bypass (<code>1' OR '1'='1</code>) just throws a syntax error, since this query concatenates
        the value <strong>without</strong> quotes. Both are covered conceptually in the notes below.
    </p>

    <h3>3. Payloads that don't apply here (and why)</h3>
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

    <h3>4. Version / dialect notes</h3>
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

    <h3>5. Why it works</h3>
    <ul>
        <li><strong>String concatenation:</strong> user input is spliced directly into the SQL text, so the
        database cannot tell the difference between "data" and "code" — anything typed becomes part of the
        command.</li>
        <li><strong>No input validation:</strong> the <code>id</code> parameter is never checked to confirm
        it's actually a number before being used in a numeric context.</li>
        <li><strong>Verbose error leakage:</strong> raw database error messages are printed straight to the
        page, which hands an attacker exact syntax feedback to iterate on — turning trial-and-error into a
        fast, guided process.</li>
    </ul>
</div>

<?php
if ($standalone) {
    echo '</div></main></div><script src="script.js"></script></body></html>';
}
