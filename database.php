<?php
$host = 'localhost';
$dbname = 'endless_bowler'; // ✅ Make sure this matches exactly!
$username = 'root';
$password = ''; // Leave empty if you have no password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
