<?php
/**
 * Route Guard: Guests Only
 * Recipe Share
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    $homeUrl = file_exists('../index.php') ? '../index.php' : 'index.php';
    header("Location: $homeUrl");
    exit;
}
