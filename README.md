# SQL Injection Training Lab

A self-contained, DVWA-style SQL injection teaching lab in plain PHP + MySQL/MariaDB.

**Local use only. Never deploy this to a public network or shared host.**

## What this is

A dashboard with a user-search box. The search input is concatenated directly into a
SQL query with no sanitization (see the `// INTENTIONALLY VULNERABLE` comment in
`search.php`), so it can be manipulated to bypass filters, enumerate tables, and leak
database metadata. A lesson panel below the search results explains how and why.

## Setup

1. **Create the database and seed data.** With MySQL/MariaDB running locally:

   ```
   mysql -u root -p < schema.sql
   ```

   This creates the `sqli_lab` database, the `users` table, and inserts 15 fake rows.

2. **Point `db.php` at your local database**, if needed. By default it connects to
   `127.0.0.1`, database `sqli_lab`, user `root`, empty password. Edit the variables
   at the top of `db.php` to match your local setup.

3. **Serve the app** with PHP's built-in server from this project folder:

   ```
   php -S localhost:8000
   ```

4. Open `http://localhost:8000/index.php` in your browser.

## Using the lab

Enter a value in the search box and submit. Try `1` first for a normal result, then
open the **Lesson** page (or scroll below your search results) for a full table of
injection payloads to try, what each one does, and why it works.

## Reminder

This app is intentionally vulnerable and has no authentication, no input validation,
and verbose error output by design. Run it only on `localhost` for learning purposes —
never expose it to a network or the internet.
