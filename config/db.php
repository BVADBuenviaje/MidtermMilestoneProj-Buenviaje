<?php
/**
 * Database Connection Configuration
 * Recipe Share - PDO Setup
 */

$host    = '127.0.0.1';
$db      = 'recipe_share';
$user    = 'root';
$pass    = ''; // Default XAMPP MySQL password is blank
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on SQL errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $GLOBALS['pdo'] = $pdo;
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}
