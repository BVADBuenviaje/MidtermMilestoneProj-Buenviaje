<?php
/**
 * Route Guard: Authenticated Users Only
 * Recipe Share
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $loginUrl = file_exists('auth/login.php') ? 'auth/login.php' : (file_exists('login.php') ? 'login.php' : '../auth/login.php');
    header("Location: $loginUrl");
    exit;
}
