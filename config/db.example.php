<?php
/**
 * Database connection — EXAMPLE / TEMPLATE
 * Forces Academy LMS
 *
 * config/db.php is listed in .gitignore, so it is never pushed to GitHub.
 * Copy this file to config/db.php and fill in the correct values below.
 *
 * LOCAL (XAMPP/WAMP) VALUES:
 *   host     = localhost
 *   user     = root
 *   password = '' (empty)
 *   database = forces_academy_lms
 *
 * INFINITYFREE (LIVE) VALUES:
 *   Find these on your InfinityFree control panel > MySQL Databases page.
 *   host     = usually something like sqlXXX.infinityfree.com
 *   user     = usually epiz_XXXXXXXX
 *   password = the one you set when creating the MySQL database
 *   database = usually epiz_XXXXXXXX_forces_academy_lms
 */

$host     = 'localhost';   // replace with InfinityFree MySQL hostname when deploying
$user     = 'root';        // replace with InfinityFree MySQL username
$password = '';            // replace with InfinityFree MySQL password
$database = 'forces_academy_lms'; // replace with the exact InfinityFree database name

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
