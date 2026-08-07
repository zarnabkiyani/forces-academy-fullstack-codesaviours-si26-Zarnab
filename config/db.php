<?php
/**
 * Database connection
 * Forces Academy LMS
 *
 * LOCAL SETUP (XAMPP/WAMP): the values below already work out of the box.
 *
 * DEPLOYING TO INFINITYFREE (Week 5): replace the 4 values below with the
 * ones shown on your InfinityFree control panel > MySQL Databases page.
 * See config/db.example.php for a template with more notes.
 */

$host     = 'localhost';           // TODO (deploy): InfinityFree MySQL hostname, e.g. sqlXXX.infinityfree.com
$user     = 'root';                // TODO (deploy): InfinityFree MySQL username, e.g. epiz_XXXXXXXX
$password = '';                    // TODO (deploy): InfinityFree MySQL password
$database = 'forces_academy_lms';  // TODO (deploy): InfinityFree database name, e.g. epiz_XXXXXXXX_forces_academy_lms

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
