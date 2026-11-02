<?php
return [
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'sqli_lab',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'session_name'  => 'sqli_lab_secure',
    'logs_per_page' => 15,

    // Absolute URL of the secure app. Used for links in alert emails (never taken from the request).
    'app_url' => 'http://localhost/sqli-lab/secure/index.php',

    // ---------------------------------------------------------------------------------------
    // Email alerts: the admin is emailed when an SQL injection attempt is detected.
    //
    //   1. Google Account (infoassurance7@gmail.com) -> Security -> 2-Step Verification -> App passwords
    //   2. Create one, then paste the 16-character code into 'app_password' below.
    //
    // !! Do NOT commit this file with the password filled in. Anyone with it can send mail as this
    // !! account. Clear it before `git commit`, or run once:
    // !!     git update-index --skip-worktree secure/config/config.php
    //
    // While 'app_password' is empty, alerts are skipped (detections are still logged).
    // ---------------------------------------------------------------------------------------
    'mail' => [
        'enabled'          => true,
        'app_password'     => 'nojh dwos emus alhm',                       // <-- PASTE GMAIL APP PASSWORD HERE
        'username'         => 'infoassurance7@gmail.com',
        'from'             => 'infoassurance7@gmail.com',
        'from_name'        => 'SQLi Lab Alerts',
        'to'               => 'infoassurance7@gmail.com', // admin who receives the alerts
        'host'             => 'smtp.gmail.com',
        'port'             => 587,                      // STARTTLS
        'timeout'          => 10,                       // seconds; a slow SMTP server can't hang the page longer
        'throttle_minutes' => 10,                       // at most one email per attacking IP per window
    ],
];
