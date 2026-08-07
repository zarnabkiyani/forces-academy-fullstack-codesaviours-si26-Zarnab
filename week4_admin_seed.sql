-- Forces Academy LMS — Week 4 admin seed
-- Import this file in phpMyAdmin: Databases > forces_academy_lms > Import
-- (Run this AFTER database.sql — the `admins` table already exists there.)
--
-- Creates one default admin account so admin/login.php has something to
-- sign in with immediately after setup:
--
--   Username: admin
--   Password: admin123
--
-- IMPORTANT: change this password (or add your own admin row with a new
-- hash) before deploying anywhere real. The hash below was generated with
-- PHP's password_hash('admin123', PASSWORD_DEFAULT).

USE forces_academy_lms;

INSERT INTO admins (username, password, email) VALUES
('admin', '$2y$10$tuO70mGYSDXPVxDIXbTf..PWYFB3TNaS4QhRAEyt7REl72xSkvAuG', 'admin@forcesacademy.test');
