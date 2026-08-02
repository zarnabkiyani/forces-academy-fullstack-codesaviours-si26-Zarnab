<?php
/**
 * Admin authentication helper.
 *
 * Admins use their OWN session — a different session name/cookie than the
 * student side of the site — so an admin being logged in on one browser
 * tab never mixes with a student session on another tab, and vice versa.
 * This must be included (or session_name() called) BEFORE session_start()
 * on every admin page, and before any HTML output.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_name('forces_admin_session');
    session_start();
}

/**
 * Call this at the very top of every protected admin page.
 * Redirects to admin/login.php if there is no active admin session.
 */
function require_admin_login() {
    if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_role'])) {
        header('Location: login.php');
        exit;
    }
}
