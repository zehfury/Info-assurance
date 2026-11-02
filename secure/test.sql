-- Run once in phpMyAdmin (database: sqli_lab) AFTER importing ../schema.sql.
-- Extends the existing `users` table (existing 30 rows are kept) and seeds the admin.

ALTER TABLE `users`
  MODIFY `fake_password_hash` varchar(64) NOT NULL DEFAULT '',
  ADD COLUMN `password_hash` varchar(255) NULL AFTER `fake_password_hash`,
  ADD COLUMN `role` enum('admin','user') NOT NULL DEFAULT 'user' AFTER `password_hash`,
  ADD COLUMN `created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `role`,
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

-- Seeded admin. Login: admin / Admin@12345  (bcrypt) — change it after first login.
INSERT INTO `users` (`username`, `first_name`, `last_name`, `email`, `fake_password_hash`, `password_hash`, `role`)
VALUES ('admin', 'System', 'Administrator', 'admin@example.test', '',
        '$2y$10$slObWrDLLzCRHRsrkH/cpuHEAkakKiXgRuXl4BxypS1.tLya8Kfku', 'admin');

-- Every detected SQL injection attempt on login / admin login / registration lands here.
CREATE TABLE IF NOT EXISTS `sqli_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `endpoint` varchar(30) NOT NULL,
  `field_name` varchar(50) NOT NULL,
  `payload` text NOT NULL,
  `matched_patterns` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `request_uri` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_sqli_logs_created` (`created_at`),
  KEY `idx_sqli_logs_endpoint` (`endpoint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
