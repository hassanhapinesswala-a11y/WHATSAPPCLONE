<?php
// db.php - PDO connection (use this include in all php files)
$DB_HOST = 'localhost';
$DB_NAME = 'dbnhswogbdqpkw';
$DB_USER = 'uyhezup6l0hgf';
$DB_PASS = 'pr634bpk3knb';
 
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
session_start();
?>
