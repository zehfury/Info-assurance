# SQL Injection Training Lab

A self-contained, DVWA-style SQL injection teaching lab in plain PHP + MySQL/MariaDB.

**Local use only. Never deploy this to a public network or shared host.**

## What this is

Two halves, side by side:

| Part | Entry | What it shows |
|---|---|---|
| **Secure app** | `/` (user login), `/admin/login` (admin), everything else through the front controller `index.php` | Login, registration, admin dashboard. Uses prepared statements, bcrypt, CSRF tokens. Every SQL injection attempt on the login, admin-login and register forms is detected (`secure/core/SqliDetector.php`) and logged to `sqli_logs`, viewable by the admin. |
| **Vulnerable lab** | `/lab` + `/lesson` (`reps.php`, `lesson.php`) | A user-search box whose input is concatenated straight into SQL (see `// INTENTIONALLY VULNERABLE` in `search.php`), with verbose errors and a lesson page of payloads to try. Attempts here are **not** logged. |

## Setup (XAMPP)

1. Put the project in `htdocs/sqli-lab` (or a junction pointing to it) and start Apache + MySQL.
2. In phpMyAdmin (or the `mysql` CLI), import in this order:
   1. `schema.sql` — creates the `sqli_lab` database, the `users` table and 30 fictional rows.
   2. `secure/test.sql` — adds login columns to `users`, seeds the admin, creates `sqli_logs`. Run once.
3. Database credentials default to `127.0.0.1`, user `root`, empty password. Change them in
   **both** `db.php` (vulnerable lab) and `secure/config/config.php` (secure app) if needed.
4. Open `http://localhost/sqli-lab/`.

## Accounts

- **Admin:** `admin` / `Admin@12345` — log in at `/admin/login` (admin accounts only). Change this after first login.
- **Users:** register at `/register`, then log in at `/` (user accounts only). The 30 seeded directory users cannot log in.

## Addresses

All addresses are relative to `http://localhost/sqli-lab/`. The `.htaccess` file turns them into clean addresses (no folders or `.php` showing); old addresses such as `secure/admin/login.php` redirect automatically. Without mod_rewrite the app falls back to `index.php?route=...`.

| Address | Page |
|---|---|
| `/` | Secure Login (users) |
| `/register` | Register |
| `/admin/login` | Admin log in |
| `/dashboard`, `/admin`, `/admin/logs`, `/admin/action-logs`, `/admin/users` | After login |
| `/vulnerable/login` | Vulnerable Login |
| `/lab`, `/lesson`, `/xss` | Vulnerable lab, Lesson, XSS lab |

`.htaccess` also blocks direct access to internal folders and `.sql`/`.md` files, and every page is sent with `Cache-Control: no-store` so the Back button after logout cannot show a logged-in page.

Every page shares `secure/bootstrap.php`, so every login gets the same protections, SQLi detection, email alerts and action logging.

## Using the lab

- **Vulnerable Login:** open it below **Secure Login** in the sidebar (`/vulnerable/login`). It deliberately concatenates both fields into a single SQL line. Use `gwen.falcon` / `a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4` for a normal lab login (the seeded `fake_password_hash` is treated as a lab password). Try `gwen.falcon' -- ` as the username with any nonempty password to demonstrate bypass. Registered secure passwords use a different column and are not lab credentials. Successful lab logins stay in a separate demo session; detected payloads appear in admin logs and trigger the existing configured, throttled email alerts.

- **Vulnerable lab:** enter `1` in the search box for a normal result, then try the payloads on the **Lesson** page.
- **Detection logs:** try the same payloads (e.g. `' OR 1=1 -- `) on the secure login form. They won't work, but they'll appear under **SQLi logs** in the admin dashboard.

## Reminder

The vulnerable lab has no authentication, no input validation and verbose error output by
design. Run it only on `localhost` for learning purposes — never expose it to a network or the internet.
